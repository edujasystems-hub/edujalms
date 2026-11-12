<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Company;
use App\Models\EmployeeGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeGroupController extends Controller
{
    public function index(): Response
    {
        $employeeGroups = EmployeeGroup::withCount(['employees', 'classGroups'])
            ->with([
                'company:id,name,code',
                'employees' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.position', 'users.company_id'),
                'classGroups' => fn ($q) => $q->select('class_groups.id', 'class_groups.name', 'class_groups.badge_color', 'class_groups.description'),
            ])
            ->latest()
            ->get();

        $allEmployees = User::where('role', 'employee')
            ->with('company:id,name,code')
            ->get(['id', 'name', 'email', 'avatar', 'position', 'company_id']);

        $allClassGroups = ClassGroup::where('is_active', true)
            ->withCount('courses')
            ->get(['id', 'name', 'badge_color', 'description']);

        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('admin/employee-groups/Index', [
            'employeeGroups' => $employeeGroups,
            'allEmployees' => $allEmployees,
            'allClassGroups' => $allClassGroups,
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
            'badge_color' => 'required|string|max:50',
            'is_active' => 'boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'class_group_ids' => 'nullable|array',
            'class_group_ids.*' => 'exists:class_groups,id',
        ]);

        $group = EmployeeGroup::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'company_id' => $validated['company_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'badge_color' => $validated['badge_color'] ?? 'indigo',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $currentUserId = $request->user()->id;

        if (! empty($validated['user_ids'])) {
            foreach ($validated['user_ids'] as $uid) {
                $group->employees()->attach($uid, [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ]);
            }
        }

        if (! empty($validated['class_group_ids'])) {
            foreach ($validated['class_group_ids'] as $cgId) {
                $group->classGroups()->attach($cgId, [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ]);
            }
        }

        return back()->with('success', "Group karyawan '{$group->name}' berhasil dibuat.");
    }

    public function update(Request $request, EmployeeGroup $employeeGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'description' => 'nullable|string',
            'badge_color' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $employeeGroup->update($validated);

        return back()->with('success', "Group karyawan '{$employeeGroup->name}' berhasil diperbarui.");
    }

    public function destroy(EmployeeGroup $employeeGroup): \Illuminate\Http\RedirectResponse
    {
        $name = $employeeGroup->name;
        $employeeGroup->delete();

        return back()->with('success', "Group karyawan '{$name}' berhasil dihapus.");
    }

    public function assignEmployees(Request $request, EmployeeGroup $employeeGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $currentUserId = $request->user()->id;

        foreach ($validated['user_ids'] as $userId) {
            $employeeGroup->employees()->syncWithoutDetaching([
                $userId => [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ],
            ]);
        }

        return back()->with('success', count($validated['user_ids']) . " karyawan berhasil di-assign ke group '{$employeeGroup->name}'.");
    }

    public function removeEmployee(Request $request, EmployeeGroup $employeeGroup, User $user): \Illuminate\Http\RedirectResponse
    {
        $employeeGroup->employees()->detach($user->id);

        return back()->with('success', "Karyawan '{$user->name}' telah dilepas dari group '{$employeeGroup->name}'.");
    }

    public function assignClassGroups(Request $request, EmployeeGroup $employeeGroup): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'class_group_ids' => 'required|array',
            'class_group_ids.*' => 'exists:class_groups,id',
        ]);

        $currentUserId = $request->user()->id;

        foreach ($validated['class_group_ids'] as $cgId) {
            $employeeGroup->classGroups()->syncWithoutDetaching([
                $cgId => [
                    'assigned_by' => $currentUserId,
                    'assigned_at' => now(),
                ],
            ]);
        }

        return back()->with('success', count($validated['class_group_ids']) . " group kelas berhasil di-assign ke group karyawan '{$employeeGroup->name}'. Seluruh anggota group otomatis mendapatkan akses.");
    }

    public function removeClassGroup(Request $request, EmployeeGroup $employeeGroup, ClassGroup $classGroup): \Illuminate\Http\RedirectResponse
    {
        $employeeGroup->classGroups()->detach($classGroup->id);

        return back()->with('success', "Group kelas '{$classGroup->name}' telah dilepas dari group karyawan '{$employeeGroup->name}'.");
    }
}
