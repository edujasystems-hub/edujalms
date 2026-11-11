<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('industry')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add role, company_id, position, department to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee'); // 'admin', 'employee'
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->json('competency_scores')->nullable();
        });

        // 3. Tutors
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('title')->nullable(); // e.g. Senior Principal Architect
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('specialization')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Class Groups / Categories
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('badge_color')->default('blue');
            $table->string('icon')->default('sparkles');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Courses / Classes
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('tutors')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('level')->default('all_levels'); // beginner, intermediate, advanced, all_levels
            $table->string('thumbnail_url')->nullable();
            $table->decimal('estimated_duration_hours', 4, 1)->default(2.0);
            $table->json('competency_impact')->nullable(); // e.g. {"leadership": 20, "problem_solving": 30}
            $table->string('status')->default('published'); // draft, published, archived
            $table->timestamps();
        });

        // 6. Lessons / Pelajaran
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(1);
            $table->integer('duration_minutes')->default(15);
            $table->string('content_type')->default('video'); // video, audio, text, pdf, mixed
            $table->string('video_url')->nullable(); // YouTube or video link
            $table->string('audio_url')->nullable(); // Audio link
            $table->longText('content_text')->nullable(); // Text / markdown
            $table->string('pdf_url')->nullable(); // PDF embed link
            $table->json('attachments')->nullable(); // [{ "title": "File.pdf", "url": "...", "size": "2.4 MB", "type": "pdf" }]
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });

        // 7. Group Enrollment: assign employee to class group
        Schema::create('class_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['class_group_id', 'user_id']);
        });

        // 8. Direct Course Enrollment & Progress
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('enrollment_source')->default('direct'); // direct, group, invitation
            $table->string('status')->default('enrolled'); // enrolled, in_progress, completed
            $table->integer('progress_percent')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'user_id']);
        });

        // 9. Lesson User Progress
        Schema::create('lesson_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->integer('last_watched_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });

        // 10. Course Invitations
        Schema::create('course_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token')->unique();
            $table->string('status')->default('pending'); // pending, accepted, expired, revoked
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_invitations');
        Schema::dropIfExists('lesson_user');
        Schema::dropIfExists('course_user');
        Schema::dropIfExists('class_group_user');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('class_groups');
        Schema::dropIfExists('tutors');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['role', 'company_id', 'position', 'department', 'avatar', 'phone', 'competency_scores']);
        });

        Schema::dropIfExists('companies');
    }
};
