<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_ku')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->boolean('accepts_submissions')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['feedback_form_id', 'version_number'], 'form_version_unique');
        });

        Schema::create('feedback_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_form_version_id')->constrained('feedback_form_versions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('label_en');
            $table->string('label_ku')->nullable();
            $table->string('label_ar')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('staff_subject_id')->constrained('staff_subjects')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('feedback_form_version_id')->constrained('feedback_form_versions')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(
                ['student_id', 'staff_subject_id', 'feedback_form_version_id'],
                'feedback_submission_unique'
            );
        });

        Schema::create('feedback_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_submission_id')->constrained('feedback_submissions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('feedback_question_id')->constrained('feedback_questions')->cascadeOnUpdate()->restrictOnDelete();
            $table->json('value');
            $table->timestamps();
            $table->unique(['feedback_submission_id', 'feedback_question_id'], 'feedback_answer_unique');
        });

        Schema::create('feedback_response_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('feedback_form_version_id')->constrained('feedback_form_versions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('staff_subject_ids');
            $table->unsignedInteger('current_question_index')->default(0);
            $table->json('answers')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'feedback_form_version_id'], 'feedback_draft_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_response_drafts');
        Schema::dropIfExists('feedback_answers');
        Schema::dropIfExists('feedback_submissions');
        Schema::dropIfExists('feedback_questions');
        Schema::dropIfExists('feedback_form_versions');
        Schema::dropIfExists('feedback_forms');
    }
};
