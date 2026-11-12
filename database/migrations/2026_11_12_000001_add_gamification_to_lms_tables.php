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
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(200)->after('estimated_duration_hours');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(50)->after('duration_minutes');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_points')->default(0)->after('competency_scores');
            $table->string('badge')->nullable()->after('total_points');
        });

        Schema::table('lesson_user', function (Blueprint $table) {
            $table->unsignedInteger('points_earned')->default(0)->after('last_watched_seconds');
        });

        Schema::table('course_user', function (Blueprint $table) {
            $table->unsignedInteger('points_earned')->default(0)->after('progress_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'badge']);
        });

        Schema::table('lesson_user', function (Blueprint $table) {
            $table->dropColumn('points_earned');
        });

        Schema::table('course_user', function (Blueprint $table) {
            $table->dropColumn('points_earned');
        });
    }
};
