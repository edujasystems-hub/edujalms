<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('course_module_id')->nullable()->after('course_id')->constrained('course_modules')->cascadeOnDelete();
            $table->json('quiz_questions')->nullable()->after('post_test');
            $table->integer('passing_score')->default(70)->after('quiz_questions');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['course_module_id']);
            $table->dropColumn(['course_module_id', 'quiz_questions', 'passing_score']);
        });

        Schema::dropIfExists('course_modules');
    }
};
