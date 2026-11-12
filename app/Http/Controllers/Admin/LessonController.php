<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function store(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'content_type' => 'required|in:video,audio,text,pdf,mixed',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'content_text' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'due_date' => 'nullable|date',
            'pre_test' => 'nullable|array',
            'post_test' => 'nullable|array',
            'points' => 'nullable|integer|min:0',
        ]);

        $validated['course_id'] = $course->id;
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(4);

        $maxSort = $course->lessons()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        $lesson = Lesson::create($validated);

        return back()->with('success', "Pelajaran '{$lesson->title}' berhasil ditambahkan ke kelas.");
    }

    public function update(Request $request, Course $course, Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'content_type' => 'required|in:video,audio,text,pdf,mixed',
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
