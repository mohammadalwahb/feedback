<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('student_staff_subject');
    }

    public function down(): void
    {
        Schema::create('student_staff_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_subject_id')->constrained('staff_subjects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'staff_subject_id'], 'student_staff_subject_unique');
        });
    }
};
