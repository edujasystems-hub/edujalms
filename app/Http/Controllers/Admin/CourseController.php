<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Company;
use App\Models\Course;
use App\Models\CourseInvitation;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Course::with(['classGroup', 'tutor', 'company'])
            ->withCount(['lessons', 'enrolledUsers']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->has('group_id') && $request->group_id) {
            $query->where('class_group_id', $request->group_id);
        }

        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }

        $courses = $query->latest()->get();
        $classGroups = ClassGroup::where('is_active', true)->get();
        $tutors = Tutor::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();
        $employees = User::where('role', 'employee')->get(['id', 'name', 'email', 'avatar', 'position', 'company_id']);

        return Inertia::render('admin/courses/Index', [
            'courses' => $courses,
            'classGroups' => $classGroups,
            'tutors' => $tutors,
            'companies' => $companies,
            'employees' => $employees,
            'filters' => [
                'search' => $request->search ?? '',
                'group_id' => $request->group_id ?? '',
                'level' => $request->level ?? '',
            ],
        ]);
    }

    public function show(Course $course): Response
    {
        $course->load([
            'classGroup',
            'tutor',
            'company',
            'lessons' => fn ($q) => $q->orderBy('sort_order', 'asc'),
            'enrolledUsers' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.position', 'users.company_id'),
            'invitations',
        ]);

        $allEmployees = User::where('role', 'employee')
            ->with('company')
            ->get(['id', 'name', 'email', 'avatar', 'position', 'company_id']);

        $classGroups = ClassGroup::where('is_active', true)->get();
        $tutors = Tutor::where('is_active', true)->get();
        $companies = Company::where('is_active', true)->get();

        return Inertia::render('admin/courses/Show', [
            'course' => $course,
            'allEmployees' => $allEmployees,
            'classGroups' => $classGroups,
            'tutors' => $tutors,
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'tutor_id' => 'nullable|exists:tutors,id',
            'company_id' => 'nullable|exists:companies,id',
            'level' => 'required|in:beginner,intermediate,advanced,all_levels',
            'estimated_duration_hours' => 'required|numeric|min:0.5',
            'thumbnail_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'due_date' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(4);
        $course = Course::create($validated);

        return redirect()->route('admin.courses.show', $course->id)
            ->with('success', "Kelas '{$course->title}' berhasil dibuat.");
    }

    public function update(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'tutor_id' => 'nullable|exists:tutors,id',
            'company_id' => 'nullable|exists:companies,id',
            'level' => 'required|in:beginner,intermediate,advanced,all_levels',
            'estimated_duration_hours' => 'required|numeric|min:0.5',
            'thumbnail_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'due_date' => 'nullable|date',
        ]);

        $course->update($validated);

        return back()->with('success', "Informasi kelas '{$course->title}' berhasil diperbarui.");
    }

    public function destroy(Course $course): \Illuminate\Http\RedirectResponse
    {
        $title = $course->title;
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', "Kelas '{$title}' berhasil dihapus.");
    }

    public function assignEmployees(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $currentUserId = $request->user()->id;

        foreach ($validated['user_ids'] as $userId) {
            $alreadyEnrolled = $course->enrolledUsers()->where('user_id', $userId)->exists();
            if (! $alreadyEnrolled) {
                $course->enrolledUsers()->attach($userId, [
                    'assigned_by' => $currentUserId,
                    'enrollment_source' => 'direct',
                    'status' => 'enrolled',
                    'progress_percent' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', count($validated['user_ids']) . ' employee berhasil di-assign ke kelas ini.');
    }

    public function removeEmployee(Request $request, Course $course, User $user): \Illuminate\Http\RedirectResponse
    {
        $course->enrolledUsers()->detach($user->id);

        return back()->with('success', "Akses employee '{$user->name}' dari kelas ini telah dicabut.");
    }

    public function inviteEmployee(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        // Check if already invited or enrolled
        if ($existingUser && $course->enrolledUsers()->where('user_id', $existingUser->id)->exists()) {
            return back()->with('error', 'Employee ini sudah terdaftar di dalam kelas.');
        }

        $invitation = CourseInvitation::create([
            'course_id' => $course->id,
            'invited_by' => $request->user()->id,
            'user_id' => $existingUser?->id,
            'email' => $validated['email'],
            'token' => 'inv_' . Str::random(32),
            'status' => 'pending',
            'expires_at' => now()->addDays(14),
        ]);

        // If employee exists already, auto enroll them or keep pending
        if ($existingUser) {
            $course->enrolledUsers()->syncWithoutDetaching([
                $existingUser->id => [
                    'assigned_by' => $request->user()->id,
                    'enrollment_source' => 'invitation',
                    'status' => 'enrolled',
                    'progress_percent' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
        }

        return back()->with('success', "Undangan ke {$validated['email']} berhasil dikirim.");
    }
}
