<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\EmployeeGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClassGroupController extends Controller
{
    public function index(): Response
    {
        $classGroups = ClassGroup::withCount(['courses', 'employees', 'employeeGroups'])
            ->with([
                'courses' => fn ($q) => $q->select('id', 'class_group_id', 'title', 'slug', 'level', 'thumbnail_url'),
                'employees' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.position', 'users.company_id'),
                'employeeGroups' => fn ($q) => $q->select('employee_groups.id', 'employee_groups.name', 'employee_groups.code', 'employee_groups.badge_color')->withCount('employees'),
            ])
            ->latest()
            ->get();

        $allEmployees = User::where('role', 'employee')
            ->with('company')
            ->get(['id', 'name', 'email', 'avatar', 'position', 'company_id']);

        $allEmployeeGroups = EmployeeGroup::withCount('employees')
            ->where('is_active', true)
            ->get(['id', 'name', 'code', 'badge_color']);

        return Inertia::render('admin/groups/Index', [
            'classGroups' => $classGroups,
            'allEmployees' => $allEmployees,
            'allEmployeeGroups' => $allEmployeeGroups,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'badge_color' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $group = ClassGroup::create($validated);

        return back()->with('success', "Grup kelas '{$group->name}' berhasil dibuat.");
    }

    public function update(Request $request, ClassGroup $classGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'badge_color' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $classGroup->update($validated);

        return back()->with('success', "Grup kelas '{$classGroup->name}' berhasil diperbarui.");
    }

    public function destroy(ClassGroup $classGroup): \Illuminate\Http\RedirectResponse
    {
        $name = $classGroup->name;
        $classGroup->delete();

        return back()->with('success', "Grup kelas '{$name}' berhasil dihapus.");
    }

    public function assignEmployees(Request $request, ClassGroup $classGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $currentUserId = $request->user()->id;

        foreach ($validated['user_ids'] as $userId) {
            $classGroup->employees()->syncWithoutDetaching([
                $userId => [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ]
            ]);
        }

        return back()->with('success', count($validated['user_ids']) . " employee berhasil di-assign ke grup '{$classGroup->name}' dan otomatis memperoleh akses ke seluruh kelas di dalamnya.");
    }

    public function removeEmployee(Request $request, ClassGroup $classGroup, User $user): \Illuminate\Http\RedirectResponse
    {
        $classGroup->employees()->detach($user->id);

        return back()->with('success', "Employee '{$user->name}' telah dilepas dari grup '{$classGroup->name}'.");
    }

    public function assignEmployeeGroups(Request $request, ClassGroup $classGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'employee_group_ids' => 'required|array',
            'employee_group_ids.*' => 'exists:employee_groups,id',
        ]);

        $currentUserId = $request->user()->id;

        foreach ($validated['employee_group_ids'] as $egId) {
            $classGroup->employeeGroups()->syncWithoutDetaching([
                $egId => [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ],
            ]);
        }

        return back()->with('success', count($validated['employee_group_ids']) . " group karyawan berhasil di-assign ke group kelas '{$classGroup->name}'. Seluruh karyawan di dalamnya otomatis memperoleh akses.");
    }

    public function removeEmployeeGroup(Request $request, ClassGroup $classGroup, EmployeeGroup $employeeGroup): \Illuminate\Http\RedirectResponse
    {
        $classGroup->employeeGroups()->detach($employeeGroup->id);

        return back()->with('success', "Group karyawan '{$employeeGroup->name}' telah dilepas dari group kelas '{$classGroup->name}'.");
    }
}
