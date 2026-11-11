<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TutorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tutor::withCount('courses');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $tutors = $query->latest()->get();

        return Inertia::render('admin/tutors/Index', [
            'tutors' => $tutors,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tutors,email',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $tutor = Tutor::create($validated);

        return back()->with('success', "Tutor '{$tutor->name}' berhasil didaftarkan.");
    }

    public function update(Request $request, Tutor $tutor): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:tutors,email,{$tutor->id}",
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $tutor->update($validated);

        return back()->with('success', "Data tutor '{$tutor->name}' berhasil diperbarui.");
    }

    public function destroy(Tutor $tutor): \Illuminate\Http\RedirectResponse
    {
        $name = $tutor->name;
        $tutor->delete();

        return back()->with('success', "Tutor '{$name}' berhasil dihapus.");
    }
}
