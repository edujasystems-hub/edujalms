<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Company::withCount(['employees', 'courses']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        $companies = $query->latest()->get();

        return Inertia::render('admin/companies/Index', [
            'companies' => $companies,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'industry' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $company = Company::create($validated);

        return back()->with('success', "Perusahaan '{$company->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Company $company): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => "required|string|max:50|unique:companies,code,{$company->id}",
            'industry' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return back()->with('success', "Data perusahaan '{$company->name}' berhasil diperbarui.");
    }

    public function destroy(Company $company): \Illuminate\Http\RedirectResponse
    {
        $name = $company->name;
        $company->delete();

        return back()->with('success', "Perusahaan '{$name}' berhasil dihapus.");
    }
}
