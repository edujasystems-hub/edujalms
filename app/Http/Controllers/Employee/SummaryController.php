<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SummaryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Get all accessible courses for this employee (direct enrollment + group assignment)
        $accessibleCourses = $user->getAccessibleCourses();
        $accessibleCourseIds = $accessibleCourses->pluck('id')->toArray();

        // Ensure course_user rows exist for group-assigned courses if not already present
        foreach ($accessibleCourses as $c) {
            $exists = DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $c->id)
                ->exists();

            if (! $exists) {
                DB::table('course_user')->insert([
                    'course_id' => $c->id,
                    'user_id' => $user->id,
                    'enrollment_source' => 'group',
                    'status' => 'enrolled',
                    'progress_percent' => 0,
                    'last_accessed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Fetch 5 Recent Classes with their progress
        $recentEnrollments = DB::table('course_user')
            ->where('user_id', $user->id)
            ->whereIn('course_id', $accessibleCourseIds)
            ->orderByRaw('COALESCE(last_accessed_at, updated_at) DESC')
            ->take(5)
            ->get();

        $recentCourseIds = $recentEnrollments->pluck('course_id')->toArray();

        $coursesById = Course::with(['tutor', 'classGroup', 'company'])
            ->withCount('lessons')
            ->whereIn('id', $recentCourseIds)
            ->get()
            ->keyBy('id');

        $userCompletedLessonCounts = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereIn('course_id', $recentCourseIds)
            ->groupBy('course_id')
            ->select('course_id', DB::raw('count(*) as count'))
            ->pluck('count', 'course_id');

        $recentClasses = $recentEnrollments->map(function ($enrollment) use ($coursesById, $userCompletedLessonCounts) {
            $course = $coursesById->get($enrollment->course_id);
            if (! $course) {
                return null;
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'thumbnail_url' => $course->thumbnail_url,
                'level' => $course->level,
                'estimated_duration_hours' => $course->estimated_duration_hours,
                'tutor' => $course->tutor ? [
                    'id' => $course->tutor->id,
                    'name' => $course->tutor->name,
                    'title' => $course->tutor->title,
                    'avatar_url' => $course->tutor->avatar_url,
                ] : null,
                'class_group' => $course->classGroup ? [
                    'id' => $course->classGroup->id,
                    'name' => $course->classGroup->name,
                    'badge_color' => $course->classGroup->badge_color,
                ] : null,
                'company' => $course->company ? [
                    'id' => $course->company->id,
                    'name' => $course->company->name,
                ] : null,
                'progress_percent' => (int) $enrollment->progress_percent,
                'status' => $enrollment->status,
                'enrollment_source' => $enrollment->enrollment_source,
                'last_accessed_at' => $enrollment->last_accessed_at,
                'total_lessons' => $course->lessons_count,
                'completed_lessons' => $userCompletedLessonCounts->get($course->id, 0),
            ];
        })->filter()->values();

        // 3. Informative Widgets
        $allEnrollments = DB::table('course_user')
            ->where('user_id', $user->id)
            ->whereIn('course_id', $accessibleCourseIds)
            ->get();

        $totalEnrolled = $allEnrollments->count();
        $completedCount = $allEnrollments->where('status', 'completed')->count();
        $inProgressCount = $allEnrollments->where('status', 'in_progress')->count();

        // Calculate estimated learning hours accumulated
        $learningHours = 0;
        foreach ($allEnrollments as $enr) {
            $course = $accessibleCourses->firstWhere('id', $enr->course_id);
            if ($course) {
                $hours = (float) $course->estimated_duration_hours;
                $pct = (int) $enr->progress_percent;
                $learningHours += ($hours * ($pct / 100));
            }
        }
        $learningHours = round($learningHours, 1);

        // 4. Competency Matrix & Spider Chart
        $competencies = $user->getFormattedCompetencies();
        $avgCompetency = count($competencies) > 0
            ? round(collect($competencies)->avg('score'))
            : 0;

        // Top Competency
        $topCompetency = collect($competencies)->sortByDesc('score')->first();

        return Inertia::render('employee/MySummary', [
            'recentClasses' => $recentClasses,
            'widgets' => [
                'total_enrolled' => $totalEnrolled,
                'completed_courses' => $completedCount,
                'in_progress_courses' => $inProgressCount,
                'learning_hours' => $learningHours,
                'average_competency' => $avgCompetency,
                'top_competency' => $topCompetency,
            ],
            'competencies' => $competencies,
            'employee' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'position' => $user->position,
                'department' => $user->department,
                'company' => $user->company,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
