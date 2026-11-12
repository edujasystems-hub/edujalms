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
        // 1. Employee Groups table
        Schema::create('employee_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('badge_color')->default('indigo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Members of employee group (Employee Group <-> User)
        Schema::create('employee_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_group_id')->constrained('employee_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['employee_group_id', 'user_id']);
        });

        // 3. Class Group to Employee Group assignment (Class Group <-> Employee Group)
        Schema::create('class_group_employee_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->foreignId('employee_group_id')->constrained('employee_groups')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['class_group_id', 'employee_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_group_employee_group');
        Schema::dropIfExists('employee_group_user');
        Schema::dropIfExists('employee_groups');
    }
};
