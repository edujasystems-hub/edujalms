<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function store(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'course_module_id' => 'nullable|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'content_type' => 'required|in:video,audio,text,pdf,mixed,pre_test,post_test',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'content_text' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'due_date' => 'nullable|date',
            'pre_test' => 'nullable|array',
            'post_test' => 'nullable|array',
            'quiz_questions' => 'nullable|array',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'points' => 'nullable|integer|min:0',
        ]);

        $validated['course_id'] = $course->id;

        // Ensure module exists if not provided
        if (empty($validated['course_module_id'])) {
            $defaultModule = $course->modules()->first() ?? CourseModule::create([
                'course_id' => $course->id,
                'title' => 'Modul 1: Materi Utama',
                'slug' => 'modul-1-' . Str::random(4),
                'sort_order' => 1,
            ]);
            $validated['course_module_id'] = $defaultModule->id;
        }

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(4);

        $maxSort = $course->lessons()->where('course_module_id', $validated['course_module_id'])->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        $lesson = Lesson::create($validated);

        return back()->with('success', "Pelajaran '{$lesson->title}' berhasil ditambahkan ke kelas.");
    }

    public function update(Request $request, Course $course, Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'course_module_id' => 'nullable|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'content_type' => 'required|in:video,audio,text,pdf,mixed,pre_test,post_test',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'content_text' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'sort_order' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'pre_test' => 'nullable|array',
            'post_test' => 'nullable|array',
            'quiz_questions' => 'nullable|array',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'points' => 'nullable|integer|min:0',
        ]);

        $lesson->update($validated);

        return back()->with('success', "Pelajaran '{$lesson->title}' berhasil diperbarui.");
    }

    public function destroy(Course $course, Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $title = $lesson->title;
        $lesson->delete();

        return back()->with('success', "Pelajaran '{$title}' berhasil dihapus.");
    }
}
