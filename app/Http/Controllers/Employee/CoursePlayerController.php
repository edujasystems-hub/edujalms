<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CoursePlayerController extends Controller
{
    public function show(Request $request, string $slug): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $course = Course::with([
            'tutor',
            'classGroup',
            'company',
            'lessons' => fn ($q) => $q->orderBy('sort_order', 'asc'),
        ])->where('slug', $slug)->firstOrFail();

        // Check access
        if (! $user->hasAccessToCourse($course)) {
            return redirect()->route('dashboard')->with('error', 'Anda belum memiliki akses ke kelas ini. Hubungi admin korporat.');
        }

        // Ensure record in course_user exists and update last_accessed_at
        $enrollment = DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            DB::table('course_user')->insert([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'enrollment_source' => 'group',
                'status' => 'in_progress',
                'progress_percent' => 0,
                'last_accessed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('course_user')
                ->where('id', $enrollment->id)
                ->update([
                    'last_accessed_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        // Get completed lessons for this user
        $completedLessonIds = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        // Determine active lesson: from query param or first uncompleted or first
        $requestedLessonSlug = $request->query('lesson');
        $activeLesson = null;

        if ($requestedLessonSlug) {
            $activeLesson = $course->lessons->firstWhere('slug', $requestedLessonSlug);
        }

        if (! $activeLesson) {
            $activeLesson = $course->lessons->first(function ($l) use ($completedLessonIds) {
                return ! in_array($l->id, $completedLessonIds);
            }) ?? $course->lessons->first();
        }

        // Format lessons list
        $lessonsFormatted = $course->lessons->map(function ($l) use ($completedLessonIds) {
            return [
                'id' => $l->id,
                'title' => $l->title,
                'slug' => $l->slug,
                'description' => $l->description,
                'sort_order' => $l->sort_order,
                'duration_minutes' => $l->duration_minutes,
                'content_type' => $l->content_type,
                'due_date' => $l->due_date ? $l->due_date->format('Y-m-d') : null,
                'has_pre_test' => ! empty($l->pre_test),
                'has_post_test' => ! empty($l->post_test),
                'is_completed' => in_array($l->id, $completedLessonIds),
                'is_preview' => $l->is_preview,
            ];
        });

        // Compute current progress
        $totalLessons = $course->lessons->count();
        $completedCount = count($completedLessonIds);
        $progressPercent = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

        $enrollment = DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $isCompleted = ($enrollment && $enrollment->status === 'completed') || ($totalLessons > 0 && $completedCount >= $totalLessons);
        $completedAt = $enrollment && $enrollment->completed_at
            ? \Illuminate\Support\Carbon::parse($enrollment->completed_at)->translatedFormat('d F Y')
            : ($isCompleted ? now()->translatedFormat('d F Y') : null);
        $certificateCode = 'EDJ-LMS-' . str_pad($course->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) . '-' . ($enrollment && $enrollment->completed_at ? date('Ymd', strtotime($enrollment->completed_at)) : date('Ymd'));

        return Inertia::render('employee/CoursePlayer', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'short_description' => $course->short_description,
                'description' => $course->description,
                'level' => $course->level,
                'estimated_duration_hours' => $course->estimated_duration_hours,
                'due_date' => $course->due_date ? $course->due_date->format('Y-m-d') : null,
                'tutor' => $course->tutor,
                'class_group' => $course->classGroup,
                'company' => $course->company,
                'progress_percent' => $progressPercent,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedCount,
                'is_completed' => $isCompleted,
                'completed_at' => $completedAt,
                'certificate_code' => $certificateCode,
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'job_title' => $user->job_title ?? $user->position,
                'department' => $user->department,
                'company' => $user->company ? ['name' => $user->company->name] : null,
            ],
            'lessons' => $lessonsFormatted,
            'activeLesson' => $activeLesson ? [
                'id' => $activeLesson->id,
                'title' => $activeLesson->title,
                'slug' => $activeLesson->slug,
                'description' => $activeLesson->description,
                'sort_order' => $activeLesson->sort_order,
                'duration_minutes' => $activeLesson->duration_minutes,
                'content_type' => $activeLesson->content_type,
                'due_date' => $activeLesson->due_date ? $activeLesson->due_date->format('Y-m-d') : null,
                'pre_test' => $activeLesson->pre_test,
                'post_test' => $activeLesson->post_test,
                'video_url' => $activeLesson->video_url,
                'audio_url' => $activeLesson->audio_url,
                'content_text' => $activeLesson->content_text,
                'pdf_url' => $activeLesson->pdf_url,
                'attachments' => $activeLesson->attachments ?? [],
                'is_completed' => in_array($activeLesson->id, $completedLessonIds),
            ] : null,
        ]);
    }

    public function toggleLessonComplete(Request $request, Course $course, Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAccessToCourse($course)) {
            abort(403);
        }

        $existing = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $newCompletedStatus = true;
        if ($existing) {
            $newCompletedStatus = ! $existing->is_completed;
            DB::table('lesson_user')
                ->where('id', $existing->id)
                ->update([
                    'is_completed' => $newCompletedStatus,
                    'completed_at' => $newCompletedStatus ? now() : null,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('lesson_user')->insert([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
                'is_completed' => true,
                'completed_at' => now(),
                'last_watched_seconds' => $lesson->duration_minutes * 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Recalculate progress for the course
        $totalLessons = $course->lessons()->count();
        $completedLessons = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->count();

        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $status = $progressPercent >= 100 ? 'completed' : ($progressPercent > 0 ? 'in_progress' : 'enrolled');

        DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update([
                'progress_percent' => $progressPercent,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
                'last_accessed_at' => now(),
                'updated_at' => now(),
            ]);

        // If completed and course has competency impact, slightly bump employee competencies
        if ($newCompletedStatus && $course->competency_impact) {
            $userScores = $user->competency_scores ?? [
                'Kepemimpinan' => 75,
                'Komunikasi' => 80,
                'Pemecahan Masalah' => 85,
                'Keahlian Teknis' => 85,
                'Manajemen Proyek' => 75,
                'Literasi Data & AI' => 80,
            ];
            foreach ($course->competency_impact as $compKey => $impactVal) {
                if (isset($userScores[$compKey])) {
                    $userScores[$compKey] = min(100, $userScores[$compKey] + 1);
                }
            }
            $user->competency_scores = $userScores;
            $user->save();
        }

        $msg = $newCompletedStatus
            ? "Pelajaran '{$lesson->title}' berhasil diselesaikan! Progress kelas: {$progressPercent}%."
            : "Status pelajaran diubah menjadi belum selesai.";

        return back()->with('success', $msg);
    }

    public function completeCourse(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAccessToCourse($course)) {
            abort(403);
        }

        // Mark all lessons completed
        foreach ($course->lessons as $lesson) {
            DB::table('lesson_user')->updateOrInsert(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                [
                    'course_id' => $course->id,
                    'is_completed' => true,
                    'completed_at' => now(),
                    'last_watched_seconds' => $lesson->duration_minutes * 60,
                    'updated_at' => now(),
                ]
            );
        }

        // Mark course completed in course_user
        DB::table('course_user')->updateOrInsert(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'progress_percent' => 100,
                'status' => 'completed',
                'completed_at' => now(),
                'last_accessed_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Competency score boost
        if ($course->competency_impact) {
            $userScores = $user->competency_scores ?? [
                'Kepemimpinan' => 75,
                'Komunikasi' => 80,
                'Pemecahan Masalah' => 85,
                'Keahlian Teknis' => 85,
                'Manajemen Proyek' => 75,
                'Literasi Data & AI' => 80,
            ];
            foreach ($course->competency_impact as $compKey => $impactVal) {
                if (isset($userScores[$compKey])) {
                    $userScores[$compKey] = min(100, $userScores[$compKey] + 2);
                }
            }
            $user->competency_scores = $userScores;
            $user->save();
        }

        return back()->with('success', "Selamat! Anda telah menandai kelas '{$course->title}' selesai dan berhak atas Sertifikat Kelulusan Resmi.");
    }
}
