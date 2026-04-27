<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('english_name');
            $table->string('kurdish_name')->nullable();
            $table->string('arabic_name')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('english_name');
            $table->string('kurdish_name')->nullable();
            $table->string('arabic_name')->nullable();
            $table->foreignId('college_id')->constrained('colleges')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staff_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('staff_employee_id');
            $table->string('instructor_name');
            $table->string('subject_name');
            $table->foreignId('college_id')->constrained('colleges')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['staff_employee_id', 'subject_name', 'college_id', 'department_id', 'semester_id'],
                'staff_subject_context_unique'
            );
        });

        Schema::create('student_staff_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_subject_id')->constrained('staff_subjects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'staff_subject_id'], 'student_staff_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_staff_subject');
        Schema::dropIfExists('staff_subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('admins');
    }
};
