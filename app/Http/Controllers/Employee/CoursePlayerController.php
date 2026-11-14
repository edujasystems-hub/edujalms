<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'modules' => fn ($q) => $q->orderBy('sort_order', 'asc')->with([
                'lessons' => fn ($l) => $l->orderBy('sort_order', 'asc'),
            ]),
            'lessons' => fn ($q) => $q->orderBy('sort_order', 'asc'),
        ])->where('slug', $slug)->firstOrFail();

        // Check access
        if (! $user->hasAccessToCourse($course)) {
            return redirect()->route('dashboard')->with('error', 'Anda belum memiliki akses ke kelas ini. Hubungi admin korporat.');
        }

        // Ensure any unassigned lessons belong to a module
        $unassignedLessons = $course->lessons()->whereNull('course_module_id')->get();
        if ($unassignedLessons->isNotEmpty()) {
            $defaultModule = $course->modules()->first() ?? CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Modul 1: Materi Utama',
                'slug' => 'modul-1-' . Str::random(4),
                'sort_order' => 1,
            ]);
            $course->lessons()->whereNull('course_module_id')->update([
                'course_module_id' => $defaultModule->id,
            ]);

            $course->load([
                'modules' => fn ($q) => $q->orderBy('sort_order', 'asc')->with([
                    'lessons' => fn ($l) => $l->orderBy('sort_order', 'asc'),
                ]),
                'lessons' => fn ($q) => $q->orderBy('sort_order', 'asc'),
            ]);
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
                'course_module_id' => $l->course_module_id,
                'title' => $l->title,
                'slug' => $l->slug,
                'description' => $l->description,
                'sort_order' => $l->sort_order,
                'duration_minutes' => $l->duration_minutes,
                'points' => (int) ($l->points ?? 50),
                'content_type' => $l->content_type,
                'due_date' => $l->due_date ? $l->due_date->format('Y-m-d') : null,
                'has_pre_test' => ! empty($l->pre_test),
                'has_post_test' => ! empty($l->post_test),
                'is_completed' => in_array($l->id, $completedLessonIds),
                'is_preview' => $l->is_preview,
            ];
        });

        // Format modules list
        $modulesFormatted = $course->modules->map(function ($mod) use ($completedLessonIds) {
            $modLessons = $mod->lessons->map(function ($l) use ($completedLessonIds) {
                return [
                    'id' => $l->id,
                    'course_module_id' => $l->course_module_id,
                    'title' => $l->title,
                    'slug' => $l->slug,
                    'description' => $l->description,
                    'sort_order' => $l->sort_order,
                    'duration_minutes' => $l->duration_minutes,
                    'points' => (int) ($l->points ?? 50),
                    'content_type' => $l->content_type,
                    'due_date' => $l->due_date ? $l->due_date->format('Y-m-d') : null,
                    'is_completed' => in_array($l->id, $completedLessonIds),
                    'is_preview' => $l->is_preview,
                ];
            });

            return [
                'id' => $mod->id,
                'title' => $mod->title,
                'slug' => $mod->slug,
                'description' => $mod->description,
                'sort_order' => $mod->sort_order,
                'lessons' => $modLessons->values()->all(),
                'completed_count' => $modLessons->where('is_completed', true)->count(),
                'total_lessons' => $modLessons->count(),
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

        $activeLessonQuestions = $activeLesson ? (
            $activeLesson->quiz_questions
            ?? ($activeLesson->pre_test['questions'] ?? ($activeLesson->post_test['questions'] ?? []))
        ) : [];

        return Inertia::render('employee/CoursePlayer', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'short_description' => $course->short_description,
                'description' => $course->description,
                'level' => $course->level,
                'estimated_duration_hours' => $course->estimated_duration_hours,
                'points' => (int) ($course->points ?? 200),
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
                'total_points' => (int) ($user->total_points ?? 0),
                'gamification_tier' => $user->gamification_tier,
                'badge' => $user->badge ?? $user->gamification_tier['badge'],
                'company' => $user->company ? ['name' => $user->company->name] : null,
            ],
            'modules' => $modulesFormatted,
            'lessons' => $lessonsFormatted,
            'activeLesson' => $activeLesson ? [
                'id' => $activeLesson->id,
                'course_module_id' => $activeLesson->course_module_id,
                'module_title' => $activeLesson->module?->title,
                'title' => $activeLesson->title,
                'slug' => $activeLesson->slug,
                'description' => $activeLesson->description,
                'sort_order' => $activeLesson->sort_order,
                'duration_minutes' => $activeLesson->duration_minutes,
                'points' => (int) ($activeLesson->points ?? 50),
                'content_type' => $activeLesson->content_type,
                'due_date' => $activeLesson->due_date ? $activeLesson->due_date->format('Y-m-d') : null,
                'quiz_questions' => $activeLessonQuestions,
                'passing_score' => (int) ($activeLesson->passing_score ?? ($activeLesson->pre_test['passing_score'] ?? ($activeLesson->post_test['passing_score'] ?? 70))),
                'pre_test' => $activeLesson->pre_test,
                'post_test' => $activeLesson->post_test,
                'video_url' => $activeLesson->video_url,
                'audio_url' => $activeLesson->audio_url,
                'content_text' => $activeLesson->content_text,
                'pdf_url' => $activeLesson->pdf_url,
                'attachments' => $activeLesson->attachments ?? [],
                'is_completed' => in_array($activeLesson->id, $completedLessonIds),
            ] : null,
            'quizResult' => session('quiz_result'),
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

        $lessonPoints = (int) ($lesson->points ?? 50);
        $newCompletedStatus = true;
        $earnedDelta = 0;

        if ($existing) {
            $newCompletedStatus = ! $existing->is_completed;
            $pointsEarned = $newCompletedStatus ? $lessonPoints : 0;
            $earnedDelta = $newCompletedStatus ? ($lessonPoints - ($existing->points_earned ?? 0)) : - ($existing->points_earned ?? 0);

            DB::table('lesson_user')
                ->where('id', $existing->id)
                ->update([
                    'is_completed' => $newCompletedStatus,
                    'points_earned' => $pointsEarned,
                    'completed_at' => $newCompletedStatus ? now() : null,
                    'updated_at' => now(),
                ]);
        } else {
            $earnedDelta = $lessonPoints;
            DB::table('lesson_user')->insert([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
                'is_completed' => true,
                'points_earned' => $lessonPoints,
                'completed_at' => now(),
                'last_watched_seconds' => $lesson->duration_minutes * 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Adjust user total_points
        if ($earnedDelta > 0) {
            $user->increment('total_points', $earnedDelta);
        } elseif ($earnedDelta < 0) {
            $user->decrement('total_points', min((int) ($user->total_points ?? 0), abs($earnedDelta)));
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

        $courseEnrollment = DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $courseBonus = (int) ($course->points ?? 200);
        $coursePointsEarned = $courseEnrollment ? ($courseEnrollment->points_earned ?? 0) : 0;

        if ($status === 'completed' && $coursePointsEarned === 0) {
            $user->increment('total_points', $courseBonus);
            $coursePointsEarned = $courseBonus;
        } elseif ($status !== 'completed' && $coursePointsEarned > 0) {
            $user->decrement('total_points', min((int) ($user->total_points ?? 0), $coursePointsEarned));
            $coursePointsEarned = 0;
        }

        DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update([
                'progress_percent' => $progressPercent,
                'status' => $status,
                'points_earned' => $coursePointsEarned,
                'completed_at' => $status === 'completed' ? ($courseEnrollment->completed_at ?? now()) : null,
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
            ? "Pelajaran '{$lesson->title}' diselesaikan! (+{$lessonPoints} XP). Progress: {$progressPercent}%."
            : "Status pelajaran diubah menjadi belum selesai.";

        return back()->with('success', $msg);
    }

    public function submitQuiz(Request $request, Course $course, Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAccessToCourse($course)) {
            abort(403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $questions = $lesson->quiz_questions
            ?? ($lesson->pre_test['questions'] ?? ($lesson->post_test['questions'] ?? []));

        if (empty($questions)) {
            return back()->with('error', 'Soal evaluasi tidak ditemukan.');
        }

        $correctCount = 0;
        foreach ($questions as $idx => $q) {
            if (isset($validated['answers'][$idx]) && (int) $validated['answers'][$idx] === (int) $q['correct_answer']) {
                $correctCount++;
            }
        }

        $scorePercent = count($questions) > 0 ? round(($correctCount / count($questions)) * 100) : 0;
        $passingScore = (int) ($lesson->passing_score ?? ($lesson->pre_test['passing_score'] ?? ($lesson->post_test['passing_score'] ?? 70)));
        $isPassed = $scorePercent >= $passingScore;

        // Pre-test completes when taken; Post-test completes if passed
        $markComplete = ($lesson->content_type === 'pre_test') || $isPassed;

        if ($markComplete) {
            $lessonPoints = (int) ($lesson->points ?? 50);

            $existing = DB::table('lesson_user')
                ->where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            if (! $existing) {
                DB::table('lesson_user')->insert([
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                    'course_id' => $course->id,
                    'is_completed' => true,
                    'points_earned' => $lessonPoints,
                    'completed_at' => now(),
                    'last_watched_seconds' => $lesson->duration_minutes * 60,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user->increment('total_points', $lessonPoints);
            } elseif (! $existing->is_completed) {
                DB::table('lesson_user')
                    ->where('id', $existing->id)
                    ->update([
                        'is_completed' => true,
                        'points_earned' => $lessonPoints,
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);

                $user->increment('total_points', $lessonPoints);
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
        }

        $testTypeLabel = $lesson->content_type === 'pre_test' ? 'Pre-Test' : 'Post-Test';
        $msg = $isPassed
            ? "Selamat! Anda lulus {$testTypeLabel} dengan skor {$scorePercent}%. (+{$lesson->points} XP)"
            : ($lesson->content_type === 'pre_test'
                ? "Hasil {$testTypeLabel}: {$scorePercent}%. Pemahaman awal Anda tercatat. Silakan lanjutkan ke materi berikutnya."
                : "Hasil {$testTypeLabel}: {$scorePercent}%. Belum mencapai target kelulusan ({$passingScore}%). Anda dapat mengulanginya.");

        return back()->with('quiz_result', [
            'lesson_id' => $lesson->id,
            'score' => $scorePercent,
            'correct' => $correctCount,
            'total' => count($questions),
            'passed' => $isPassed,
            'answers' => $validated['answers'],
        ])->with('success', $msg);
    }

    public function completeCourse(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAccessToCourse($course)) {
            abort(403);
        }

        $totalAddedPoints = 0;

        // Mark all lessons completed & calculate missing lesson XP
        foreach ($course->lessons as $lesson) {
            $existing = DB::table('lesson_user')
                ->where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            $lessonPoints = (int) ($lesson->points ?? 50);

            if (! $existing) {
                DB::table('lesson_user')->insert([
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                    'course_id' => $course->id,
                    'is_completed' => true,
                    'points_earned' => $lessonPoints,
                    'completed_at' => now(),
                    'last_watched_seconds' => $lesson->duration_minutes * 60,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalAddedPoints += $lessonPoints;
            } elseif (! $existing->is_completed) {
                $missingPoints = max(0, $lessonPoints - ($existing->points_earned ?? 0));
                DB::table('lesson_user')
                    ->where('id', $existing->id)
                    ->update([
                        'is_completed' => true,
                        'points_earned' => $lessonPoints,
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
                $totalAddedPoints += $missingPoints;
            }
        }

        // Award course completion bonus points if not yet awarded
        $courseEnrollment = DB::table('course_user')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $courseBonus = (int) ($course->points ?? 200);
        $coursePointsEarned = $courseEnrollment ? ($courseEnrollment->points_earned ?? 0) : 0;

        if ($coursePointsEarned === 0) {
            $totalAddedPoints += $courseBonus;
            $coursePointsEarned = $courseBonus;
        }

        // Mark course completed in course_user
        DB::table('course_user')->updateOrInsert(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'progress_percent' => 100,
                'status' => 'completed',
                'points_earned' => $coursePointsEarned,
                'completed_at' => now(),
                'last_accessed_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($totalAddedPoints > 0) {
            $user->increment('total_points', $totalAddedPoints);
        }

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

        return back()->with('success', "Selamat! Anda telah menandai kelas '{$course->title}' selesai, mendapatkan +{$totalAddedPoints} XP dan Sertifikat Kelulusan Resmi.");
    }
}
