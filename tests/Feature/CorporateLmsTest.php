<?php

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\EmployeeGroup;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('admin can view admin dashboard and manage lms', function () {
    $admin = User::where('role', 'admin')->first();

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);

    $adminDash = $this->actingAs($admin)->get('/admin/dashboard');
    $adminDash->assertStatus(200);

    $coursesResponse = $this->actingAs($admin)->get('/admin/courses');
    $coursesResponse->assertStatus(200);

    $groupsResponse = $this->actingAs($admin)->get('/admin/groups');
    $groupsResponse->assertStatus(200);

    $tutorsResponse = $this->actingAs($admin)->get('/admin/tutors');
    $tutorsResponse->assertStatus(200);

    $companiesResponse = $this->actingAs($admin)->get('/admin/companies');
    $companiesResponse->assertStatus(200);

    $employeesResponse = $this->actingAs($admin)->get('/admin/employees');
    $employeesResponse->assertStatus(200);
});

test('employee sees my summary with recent courses and spider chart data', function () {
    $employee = User::where('role', 'employee')->first();

    $response = $this->actingAs($employee)->get('/dashboard');
    $response->assertStatus(200);

    $summaryResponse = $this->actingAs($employee)->get('/my-summary');
    $summaryResponse->assertStatus(200);
    $summaryResponse->assertInertia(fn ($page) => $page
        ->component('employee/MySummary')
        ->has('recentClasses')
        ->has('widgets')
        ->has('competencies')
    );
});

test('employee automatically has access to all courses in assigned class group', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'email_verified_at' => now(),
    ]);

    $group = ClassGroup::create([
        'name' => 'AI Leadership Track',
        'slug' => 'ai-leadership-track',
        'badge_color' => 'indigo',
    ]);

    $course = Course::create([
        'class_group_id' => $group->id,
        'title' => 'Executive AI Transformation',
        'slug' => 'executive-ai-transformation',
        'level' => 'intermediate',
        'status' => 'published',
        'estimated_duration_hours' => 3,
    ]);

    Lesson::create([
        'course_id' => $course->id,
        'title' => 'Pengenalan AI Eksekutif',
        'slug' => 'pengenalan-ai-eksekutif',
        'content_type' => 'video',
        'video_url' => 'https://www.youtube.com/watch?v=aircAruvnKk',
        'duration_minutes' => 15,
        'sort_order' => 1,
    ]);

    // Before group assignment: user does not have access
    expect($employee->hasAccessToCourse($course))->toBeFalse();

    // Assign employee to class group
    $employee->classGroups()->attach($group->id, ['assigned_at' => now()]);

    // Now employee automatically has access to the course!
    expect($employee->fresh()->hasAccessToCourse($course))->toBeTrue();

    // Player access succeeds
    $playerResponse = $this->actingAs($employee)->get("/learn/{$course->slug}");
    $playerResponse->assertStatus(200);
    $playerResponse->assertInertia(fn ($page) => $page
        ->component('employee/CoursePlayer')
        ->has('course')
        ->has('lessons', 1)
        ->has('activeLesson')
    );
});

test('employee can toggle lesson completion and progress is calculated', function () {
    $employee = User::where('role', 'employee')->first();
    $course = Course::first();
    
    // Pick an uncompleted lesson
    $lesson = $course->lessons->first(function ($l) use ($employee) {
        return ! DB::table('lesson_user')
            ->where('user_id', $employee->id)
            ->where('lesson_id', $l->id)
            ->where('is_completed', true)
            ->exists();
    });

    $response = $this->actingAs($employee)->post("/learn/{$course->id}/lessons/{$lesson->id}/toggle");
    $response->assertRedirect();

    $hasCompleted = DB::table('lesson_user')
        ->where('user_id', $employee->id)
        ->where('lesson_id', $lesson->id)
        ->value('is_completed');

    expect((bool) $hasCompleted)->toBeTrue();
});

test('non-admin is prevented from accessing admin panel', function () {
    $employee = User::where('role', 'employee')->first();

    $response = $this->actingAs($employee)->get('/admin/courses');
    $response->assertRedirect('/dashboard');
});

test('employee can mark course complete and receive certificate data', function () {
    $employee = User::where('role', 'employee')->first();
    $course = Course::first();

    $response = $this->actingAs($employee)->post("/learn/{$course->id}/complete");
    $response->assertRedirect();

    $enrollment = DB::table('course_user')
        ->where('user_id', $employee->id)
        ->where('course_id', $course->id)
        ->first();

    expect($enrollment->status)->toBe('completed');
    expect((int) $enrollment->progress_percent)->toBe(100);
    expect($enrollment->completed_at)->not->toBeNull();

    // Verify player response has certificate details
    $playerResponse = $this->actingAs($employee)->get("/learn/{$course->slug}");
    $playerResponse->assertInertia(fn ($page) => $page
        ->component('employee/CoursePlayer')
        ->where('course.is_completed', true)
        ->has('course.certificate_code')
        ->has('course.completed_at')
    );
});

test('employee earns points when completing lesson and course', function () {
    $employee = User::where('role', 'employee')->first();
    $initialPoints = (int) ($employee->total_points ?? 0);

    $course = Course::with('lessons')->first();
    $lesson = $course->lessons->first();

    // Toggle lesson
    $this->actingAs($employee)->post("/learn/{$course->id}/lessons/{$lesson->id}/toggle");
    $employee->refresh();

    expect($employee->total_points)->toBeGreaterThanOrEqual($initialPoints);

    // Complete course
    $this->actingAs($employee)->post("/learn/{$course->id}/complete");
    $employee->refresh();

    expect($employee->total_points)->toBeGreaterThan($initialPoints);
    expect($employee->gamification_tier)->toBeArray();
    expect($employee->gamification_tier['name'])->not->toBeEmpty();
});

test('employee and admin can view leaderboard', function () {
    $employee = User::where('role', 'employee')->first();
    $admin = User::where('role', 'admin')->first();

    $employeeResponse = $this->actingAs($employee)->get('/leaderboard');
    $employeeResponse->assertOk();
    $employeeResponse->assertInertia(fn ($page) => $page
        ->component('Leaderboard')
        ->where('isAdmin', false)
        ->has('leaderboard')
        ->has('podium')
    );

    $adminResponse = $this->actingAs($admin)->get('/admin/leaderboard');
    $adminResponse->assertOk();
    $adminResponse->assertInertia(fn ($page) => $page
        ->component('Leaderboard')
        ->where('isAdmin', true)
        ->has('companies')
        ->has('leaderboard')
    );
});

test('admin can manage employee groups and assign class groups to employee groups', function () {
    $admin = User::where('role', 'admin')->first();
    $employee = User::where('role', 'employee')->first();
    $classGroup = ClassGroup::first();

    // 1. Admin views employee groups page
    $this->actingAs($admin)->get('/admin/employee-groups')->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/employee-groups/Index')
            ->has('employeeGroups')
            ->has('allEmployees')
            ->has('allClassGroups')
        );

    // 2. Admin creates employee group
    $response = $this->actingAs($admin)->post('/admin/employee-groups', [
        'name' => 'Backend Core Team',
        'code' => 'GRP-BE-01',
        'badge_color' => 'indigo',
        'is_active' => true,
    ]);
    $response->assertRedirect();

    $employeeGroup = EmployeeGroup::where('name', 'Backend Core Team')->first();
    expect($employeeGroup)->not->toBeNull();

    // 3. Admin assigns employee to employee group
    $this->actingAs($admin)->post("/admin/employee-groups/{$employeeGroup->id}/assign-employees", [
        'user_ids' => [$employee->id],
    ])->assertRedirect();

    expect($employeeGroup->employees()->where('users.id', $employee->id)->exists())->toBeTrue();

    // 4. Admin assigns class group to employee group
    $this->actingAs($admin)->post("/admin/employee-groups/{$employeeGroup->id}/assign-class-groups", [
        'class_group_ids' => [$classGroup->id],
    ])->assertRedirect();

    expect($employeeGroup->classGroups()->where('class_groups.id', $classGroup->id)->exists())->toBeTrue();

    // 5. Verify employee now automatically has access to all courses in the class group
    $coursesInClassGroup = Course::where('class_group_id', $classGroup->id)->get();
    expect($coursesInClassGroup->count())->toBeGreaterThan(0);

    foreach ($coursesInClassGroup as $c) {
        expect($employee->hasAccessToCourse($c))->toBeTrue();
    }

    $accessibleCourses = $employee->getAccessibleCourses();
    foreach ($coursesInClassGroup as $c) {
        expect($accessibleCourses->contains('id', $c->id))->toBeTrue();
    }
});

test('admin can manage course modules and configure pre-test and post-test multiple choice questions', function () {
    $admin = User::where('role', 'admin')->first();
    $course = Course::first();

    // 1. Admin creates a module
    $response = $this->actingAs($admin)->post("/admin/courses/{$course->id}/modules", [
        'title' => 'Modul Khusus: AI Ethics',
        'description' => 'Diskusi etika dan regulasi AI',
        'sort_order' => 10,
    ]);
    $response->assertRedirect();

    $module = CourseModule::where('title', 'Modul Khusus: AI Ethics')->first();
    expect($module)->not->toBeNull();
    expect($module->course_id)->toBe($course->id);

    // 2. Admin configures Pre-Test for this module
    $preTestResponse = $this->actingAs($admin)->post("/admin/courses/{$course->id}/modules/{$module->id}/tests", [
        'test_type' => 'pre_test',
        'is_active' => true,
        'title' => 'Pre-Test: Pemahaman Etika AI',
        'instructions' => 'Jawab soal sebelum belajar.',
        'passing_score' => 80,
        'duration_minutes' => 10,
        'points' => 30,
        'questions' => [
            [
                'question' => 'Apakah data pelanggan boleh dipublikasikan?',
                'options' => ['Boleh', 'Tidak Boleh', 'Terserah', 'Ragu-ragu'],
                'correct_answer' => 1,
                'explanation' => 'Data pribadi wajib dilindungi UU PDP.',
            ],
        ],
    ]);
    $preTestResponse->assertRedirect();

    $preTestLesson = $module->preTestLesson();
    expect($preTestLesson)->not->toBeNull();
    expect($preTestLesson->content_type)->toBe('pre_test');
    expect($preTestLesson->passing_score)->toBe(80);
    expect(count($preTestLesson->quiz_questions))->toBe(1);

    // 3. Admin configures Post-Test for this module
    $postTestResponse = $this->actingAs($admin)->post("/admin/courses/{$course->id}/modules/{$module->id}/tests", [
        'test_type' => 'post_test',
        'is_active' => true,
        'title' => 'Post-Test: Sertifikasi Etika AI',
        'instructions' => 'Evaluasi kelulusan modul.',
        'passing_score' => 70,
        'duration_minutes' => 15,
        'points' => 50,
        'questions' => [
            [
                'question' => 'Apa sanksi pelanggaran UU PDP?',
                'options' => ['Denda administratif dan pidana', 'Hadiah', 'Libur', 'Pujian'],
                'correct_answer' => 0,
                'explanation' => 'UU PDP mengatur sanksi tegas.',
            ],
        ],
    ]);
    $postTestResponse->assertRedirect();

    $postTestLesson = $module->postTestLesson();
    expect($postTestLesson)->not->toBeNull();
    expect($postTestLesson->content_type)->toBe('post_test');
    expect($postTestLesson->passing_score)->toBe(70);

    // 4. Admin updates the module
    $this->actingAs($admin)->put("/admin/courses/{$course->id}/modules/{$module->id}", [
        'title' => 'Modul Khusus: AI Ethics Updated',
        'description' => 'Deskripsi baru',
        'sort_order' => 12,
    ])->assertRedirect();

    expect($module->fresh()->title)->toBe('Modul Khusus: AI Ethics Updated');
});

test('employee course player loads modules and employee can submit multiple choice quiz', function () {
    $employee = User::where('role', 'employee')->first();
    $course = Course::with('modules.lessons')->first();

    // Ensure access
    DB::table('course_user')->updateOrInsert(
        ['user_id' => $employee->id, 'course_id' => $course->id],
        ['status' => 'in_progress', 'progress_percent' => 10, 'last_accessed_at' => now(), 'created_at' => now(), 'updated_at' => now()]
    );

    // Course player page response
    $response = $this->actingAs($employee)->get("/learn/{$course->slug}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('employee/CoursePlayer')
        ->has('course')
        ->has('modules')
        ->has('lessons')
        ->has('activeLesson')
    );

    // Find a quiz lesson (pre_test or post_test)
    $quizLesson = $course->lessons()->whereIn('content_type', ['pre_test', 'post_test'])->first();
    expect($quizLesson)->not->toBeNull();

    // Submit quiz with correct answer
    $questions = $quizLesson->quiz_questions;
    $answers = [];
    foreach ($questions as $idx => $q) {
        $answers[$idx] = $q['correct_answer'];
    }

    $quizResponse = $this->actingAs($employee)->post("/learn/{$course->id}/lessons/{$quizLesson->id}/quiz", [
        'answers' => $answers,
    ]);
    $quizResponse->assertRedirect();

    // Verify completion recorded in DB
    $isCompletedInDb = DB::table('lesson_user')
        ->where('user_id', $employee->id)
        ->where('lesson_id', $quizLesson->id)
        ->where('is_completed', true)
        ->exists();

    expect($isCompletedInDb)->toBeTrue();
});

