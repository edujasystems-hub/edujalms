<?php

use App\Http\Controllers\Admin\ClassGroupController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeGroupController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\TutorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\CourseCatalogController;
use App\Http\Controllers\Employee\CoursePlayerController;
use App\Http\Controllers\Employee\SummaryController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return inertia('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Universal Dashboard (routes dynamically to Admin Dashboard or Employee Summary)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Routes
    Route::get('/my-summary', [SummaryController::class, 'index'])->name('employee.summary');
    Route::get('/my-courses', [CourseCatalogController::class, 'index'])->name('employee.courses');
    Route::get('/leaderboard', [LeaderboardController::class, 'employeeIndex'])->name('employee.leaderboard');
    Route::get('/learn/{slug}', [CoursePlayerController::class, 'show'])->name('employee.learn.show');
    Route::post('/learn/{course}/lessons/{lesson}/toggle', [CoursePlayerController::class, 'toggleLessonComplete'])
        ->name('employee.learn.toggle');
    Route::post('/learn/{course}/complete', [CoursePlayerController::class, 'completeCourse'])
        ->name('employee.learn.complete');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/leaderboard', [LeaderboardController::class, 'adminIndex'])->name('leaderboard');

        // Course Management (Pengelolaan Kelas)
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/assign', [CourseController::class, 'assignEmployees'])->name('courses.assign');
        Route::delete('/courses/{course}/remove-user/{user}', [CourseController::class, 'removeEmployee'])->name('courses.remove-user');
        Route::post('/courses/{course}/invite', [CourseController::class, 'inviteEmployee'])->name('courses.invite');

        // Lesson Management (Pengelolaan Pelajaran)
        Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::put('/courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

        // Class Groups / Categories (Pengelolaan Group/Kategori Kelas)
        Route::get('/groups', [ClassGroupController::class, 'index'])->name('groups.index');
        Route::post('/groups', [ClassGroupController::class, 'store'])->name('groups.store');
        Route::put('/groups/{classGroup}', [ClassGroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{classGroup}', [ClassGroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{classGroup}/assign', [ClassGroupController::class, 'assignEmployees'])->name('groups.assign');
        Route::delete('/groups/{classGroup}/remove-user/{user}', [ClassGroupController::class, 'removeEmployee'])->name('groups.remove-user');
        Route::post('/groups/{classGroup}/assign-employee-groups', [ClassGroupController::class, 'assignEmployeeGroups'])->name('groups.assign-employee-groups');
        Route::delete('/groups/{classGroup}/remove-employee-group/{employeeGroup}', [ClassGroupController::class, 'removeEmployeeGroup'])->name('groups.remove-employee-group');

        // Employee Groups (Pengelolaan Group Karyawan & Assign Group Kelas)
        Route::get('/employee-groups', [EmployeeGroupController::class, 'index'])->name('employee-groups.index');
        Route::post('/employee-groups', [EmployeeGroupController::class, 'store'])->name('employee-groups.store');
        Route::put('/employee-groups/{employeeGroup}', [EmployeeGroupController::class, 'update'])->name('employee-groups.update');
        Route::delete('/employee-groups/{employeeGroup}', [EmployeeGroupController::class, 'destroy'])->name('employee-groups.destroy');
        Route::post('/employee-groups/{employeeGroup}/assign-employees', [EmployeeGroupController::class, 'assignEmployees'])->name('employee-groups.assign-employees');
        Route::delete('/employee-groups/{employeeGroup}/remove-employee/{user}', [EmployeeGroupController::class, 'removeEmployee'])->name('employee-groups.remove-employee');
        Route::post('/employee-groups/{employeeGroup}/assign-class-groups', [EmployeeGroupController::class, 'assignClassGroups'])->name('employee-groups.assign-class-groups');
        Route::delete('/employee-groups/{employeeGroup}/remove-class-group/{classGroup}', [EmployeeGroupController::class, 'removeClassGroup'])->name('employee-groups.remove-class-group');

        // Tutors (Pengelolaan Tutor)
        Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
        Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
        Route::put('/tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');
        Route::delete('/tutors/{tutor}', [TutorController::class, 'destroy'])->name('tutors.destroy');

        // Companies (Pengelolaan Perusahaan)
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        // Employees (Pengelolaan Karyawan & Hak Akses)
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{user}/access', [EmployeeController::class, 'updateAccess'])->name('employees.access');
    });
});

require __DIR__.'/settings.php';
