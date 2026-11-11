<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CourseCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $accessibleCourses = $user->getAccessibleCourses();
        $accessibleCourseIds = $accessibleCourses->pluck('id')->toArray();

        $enrollments = DB::table('course_user')
            ->where('user_id', $user->id)
            ->whereIn('course_id', $accessibleCourseIds)
            ->get()
            ->keyBy('course_id');

        $coursesWithProgress = $accessibleCourses->map(function ($c) use ($enrollments) {
            $enr = $enrollments->get($c->id);
            return [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'short_description' => $c->short_description,
                'thumbnail_url' => $c->thumbnail_url,
                'level' => $c->level,
                'estimated_duration_hours' => $c->estimated_duration_hours,
                'tutor' => $c->tutor,
                'class_group' => $c->classGroup,
                'company' => $c->company,
                'lessons_count' => $c->lessons()->count(),
                'progress_percent' => $enr ? (int) $enr->progress_percent : 0,
                'status' => $enr ? $enr->status : 'enrolled',
                'enrollment_source' => $enr ? $enr->enrollment_source : 'group',
            ];
        });

        // Assigned Groups
        $assignedGroups = $user->classGroups()->withCount('courses')->get();

        return Inertia::render('employee/Catalog', [
            'courses' => $coursesWithProgress,
            'assignedGroups' => $assignedGroups,
        ]);
    }
}
