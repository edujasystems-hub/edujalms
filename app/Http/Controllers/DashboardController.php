<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Employee\SummaryController;
use App\Models\ClassGroup;
use App\Models\Company;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user && $user->isEmployee()) {
            return app(SummaryController::class)->index($request);
        }

        return $this->adminDashboard($request);
    }

    public function adminDashboard(Request $request): Response
    {
        // Admin Dashboard
        $totalCompanies = Company::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $totalCourses = Course::count();
        $totalLessons = Lesson::count();
        $totalGroups = ClassGroup::count();

        $totalEnrollments = DB::table('course_user')->count();
        $completedEnrollments = DB::table('course_user')->where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0;

        $recentCourses = Course::with(['classGroup', 'tutor', 'company'])
            ->withCount(['lessons', 'enrolledUsers'])
            ->latest()
            ->take(6)
            ->get();

        $recentEnrollments = DB::table('course_user')
            ->join('users', 'course_user.user_id', '=', 'users.id')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->select(
                'course_user.id',
                'course_user.progress_percent',
                'course_user.status',
                'course_user.enrollment_source',
                'course_user.created_at',
                'course_user.last_accessed_at',
                'users.name as user_name',
                'users.email as user_email',
                'users.avatar as user_avatar',
                'courses.title as course_title',
                'courses.slug as course_slug'
            )
            ->orderBy('course_user.updated_at', 'desc')
            ->take(6)
            ->get();

        $classGroups = ClassGroup::withCount(['courses', 'employees'])
            ->latest()
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_companies' => $totalCompanies,
                'total_employees' => $totalEmployees,
                'total_courses' => $totalCourses,
                'total_lessons' => $totalLessons,
                'total_groups' => $totalGroups,
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $completionRate,
            ],
            'recentCourses' => $recentCourses,
            'recentEnrollments' => $recentEnrollments,
            'classGroups' => $classGroups,
        ]);
    }
}
