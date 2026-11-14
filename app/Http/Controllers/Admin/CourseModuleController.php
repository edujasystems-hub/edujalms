<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseModuleController extends Controller
{
    public function store(Request $request, Course $course): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $maxSort = $course->modules()->max('sort_order') ?? 0;
        $validated['course_id'] = $course->id;
        $validated['sort_order'] = $validated['sort_order'] ?? ($maxSort + 1);
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(4);

        $module = CourseModule::create($validated);

        return back()->with('success', "Modul '{$module->title}' berhasil dibuat.");
    }

    public function update(Request $request, Course $course, CourseModule $module): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $module->update($validated);

        return back()->with('success', "Modul '{$module->title}' berhasil diperbarui.");
    }

    public function destroy(Course $course, CourseModule $module): \Illuminate\Http\RedirectResponse
    {
        $title = $module->title;
        $module->delete();

        return back()->with('success', "Modul '{$title}' dan seluruh pelajarannya berhasil dihapus.");
    }

    public function saveTest(Request $request, Course $course, CourseModule $module): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'test_type' => 'required|in:pre_test,post_test',
            'is_active' => 'required|boolean',
            'title' => 'required_if:is_active,true|nullable|string|max:255',
            'instructions' => 'nullable|string',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'duration_minutes' => 'nullable|integer|min:1',
            'points' => 'nullable|integer|min:0',
            'questions' => 'required_if:is_active,true|nullable|array',
            'questions.*.question' => 'required_if:is_active,true|string',
            'questions.*.options' => 'required_if:is_active,true|array|min:2',
            'questions.*.correct_answer' => 'required_if:is_active,true|integer',
            'questions.*.explanation' => 'nullable|string',
        ]);

        $testType = $validated['test_type'];
        $existing = Lesson::where('course_module_id', $module->id)
            ->where('content_type', $testType)
            ->first();

        $testLabel = $testType === 'pre_test' ? 'Pre-Test' : 'Post-Test';

        if (! $validated['is_active']) {
            if ($existing) {
                $existing->delete();
            }
            return back()->with('success', "{$testLabel} untuk modul '{$module->title}' dinonaktifkan.");
        }

        $title = ! empty($validated['title'])
            ? $validated['title']
            : ($testType === 'pre_test' ? "Pre-Test: {$module->title}" : "Post-Test: {$module->title}");

        $questions = $validated['questions'] ?? [];
        // Ensure each question has a unique ID
        foreach ($questions as $idx => &$q) {
            if (empty($q['id'])) {
                $q['id'] = 'q_' . ($idx + 1) . '_' . Str::random(4);
            }
        }

        $lessonData = [
            'course_id' => $course->id,
            'course_module_id' => $module->id,
            'title' => $title,
            'description' => $validated['instructions'] ?? ($testType === 'pre_test' ? 'Uji pemahaman awal Anda sebelum mempelajari materi modul ini.' : 'Evaluasi akhir untuk menguji penguasaan materi modul ini.'),
            'content_type' => $testType,
            'quiz_questions' => $questions,
            'passing_score' => $validated['passing_score'] ?? 70,
            'duration_minutes' => $validated['duration_minutes'] ?? 15,
            'points' => $validated['points'] ?? 50,
            'sort_order' => $testType === 'pre_test' ? 0 : 999,
        ];

        if ($existing) {
            $existing->update($lessonData);
        } else {
            $lessonData['slug'] = Str::slug($title) . '-' . Str::random(4);
            Lesson::create($lessonData);
        }

        return back()->with('success', "{$testLabel} untuk modul '{$module->title}' berhasil disimpan.");
    }
}
