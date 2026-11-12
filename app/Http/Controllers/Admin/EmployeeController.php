<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('role', 'employee')
            ->with(['company', 'classGroups', 'enrolledCourses', 'employeeGroups'])
            ->withCount(['classGroups', 'enrolledCourses', 'employeeGroups']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $employees = $query->latest()->get();
        $companies = Company::where('is_active', true)->get();
        $classGroups = ClassGroup::where('is_active', true)->get();
        $courses = Course::where('status', 'published')->get(['id', 'title', 'class_group_id']);

        return Inertia::render('admin/employees/Index', [
            'employees' => $employees,
            'companies' => $companies,
            'classGroups' => $classGroups,
            'courses' => $courses,
            'filters' => [
                'search' => $request->search ?? '',
                'company_id' => $request->company_id ?? '',
            ],
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'company_id' => 'nullable|exists:companies,id',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:class_groups,id',
        ]);

        $employee = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee',
            'company_id' => $validated['company_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email_verified_at' => now(),
            'competency_scores' => [
                'Kepemimpinan' => 70,
                'Komunikasi' => 75,
                'Pemecahan Masalah' => 80,
                'Keahlian Teknis' => 80,
                'Manajemen Proyek' => 70,
                'Literasi Data & AI' => 70,
            ],
        ]);

        if (! empty($validated['group_ids'])) {
            $employee->classGroups()->sync($validated['group_ids']);
        }

        return back()->with('success', "Employee '{$employee->name}' berhasil didaftarkan.");
    }

    public function updateAccess(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:class_groups,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $user->classGroups()->sync($validated['group_ids'] ?? []);

        // Sync direct courses
        $existingPivot = $user->enrolledCourses()->get()->keyBy('id');
        $newCourseIds = $validated['course_ids'] ?? [];

        $syncData = [];
        foreach ($newCourseIds as $cid) {
            if ($existingPivot->has($cid)) {
                $prev = $existingPivot->get($cid)->pivot;
                $syncData[$cid] = [
                    'enrollment_source' => $prev->enrollment_source ?? 'direct',
                    'status' => $prev->status ?? 'enrolled',
                    'progress_percent' => $prev->progress_percent ?? 0,
                    'last_accessed_at' => $prev->last_accessed_at ?? null,
                ];
            } else {
                $syncData[$cid] = [
                    'assigned_by' => $request->user()->id,
                    'enrollment_source' => 'direct',
                    'status' => 'enrolled',
                    'progress_percent' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        $user->enrolledCourses()->sync($syncData);

        return back()->with('success', "Hak akses kelas dan grup untuk '{$user->name}' berhasil diperbarui.");
    }
}
