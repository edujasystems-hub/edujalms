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
            $table->date('due_date')->nullable()->after('status');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('is_preview');
            $table->json('pre_test')->nullable()->after('due_date');
            $table->json('post_test')->nullable()->after('pre_test');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'pre_test', 'post_test']);
        });
    }
};
