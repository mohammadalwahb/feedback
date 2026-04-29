<?php

namespace App\Services;

use App\Enums\FeedbackFormStatus;
use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackAnswer;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponseDraft;
use App\Models\FeedbackSubmission;
use App\Models\Student;
use App\Models\StaffSubject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentFeedbackService
{
    public function currentVersionForStudent(?Student $student = null): ?FeedbackFormVersion
    {
        $v = FeedbackFormVersion::query()
            ->where('accepts_submissions', true)
            ->whereHas('form', fn ($q) => $q->where('status', FeedbackFormStatus::Active))
            ->orderByDesc('version_number')
            ->first();

        if (! $v || ! $v->isOpenForStudents()) {
            return null;
        }

        return $v;
    }

    public function assignedStaff(Student $student): \Illuminate\Support\Collection
    {
        return $student->evaluatableStaffSubjectsQuery()->get();
    }

    public function progress(Student $student, FeedbackFormVersion $version): array
    {
        $total = $student->evaluatableStaffSubjectsQuery()->count();
        $done = FeedbackSubmission::query()
            ->where('student_id', $student->id)
            ->where('feedback_form_version_id', $version->id)
            ->distinct()
            ->count('staff_subject_id');

        return ['completed' => $done, 'total' => $total];
    }

    /**
     * @param  list<int>  $staffSubjectIds
     * @param  array<string, array<string, mixed>>  $answersByStaffSerialized questionId as string key inner: value payload
     */
    public function submit(Student $student, FeedbackFormVersion $version, array $staffSubjectIds, array $answersByStaffSerialized): void
    {
        if (! $version->isOpenForStudents()) {
            throw ValidationException::withMessages(['form' => __('feedback.closed')]);
        }

        $allowedIds = $student->evaluatableStaffSubjectIds();
        foreach ($staffSubjectIds as $sid) {
            if (! in_array((int) $sid, $allowedIds, true)) {
                throw ValidationException::withMessages(['staff' => __('feedback.staff_not_in_context')]);
            }
        }

        $questions = $version->questions()->get();
        if ($questions->isEmpty()) {
            throw ValidationException::withMessages(['form' => __('feedback.no_questions')]);
        }

        DB::transaction(function () use ($student, $version, $staffSubjectIds, $answersByStaffSerialized, $questions) {
            foreach ($staffSubjectIds as $staffSubjectId) {
                if (FeedbackSubmission::query()->where([
                    'student_id' => $student->id,
                    'staff_subject_id' => $staffSubjectId,
                    'feedback_form_version_id' => $version->id,
                ])->exists()) {
                    throw ValidationException::withMessages(['staff' => __('feedback.already_submitted')]);
                }

                $submission = FeedbackSubmission::query()->create([
                    'student_id' => $student->id,
                    'staff_subject_id' => $staffSubjectId,
                    'feedback_form_version_id' => $version->id,
                    'submitted_at' => now(),
                ]);

                $perStaff = $answersByStaffSerialized[(string) $staffSubjectId] ?? $answersByStaffSerialized[$staffSubjectId] ?? [];

                foreach ($questions as $question) {
                    $payload = $perStaff[(string) $question->id] ?? $perStaff[$question->id] ?? null;
                    $normalized = $this->normalizeAnswer($question, $payload);
                    if ($normalized === null && $question->is_required) {
                        throw ValidationException::withMessages([
                            'answers' => __('validation.required', ['attribute' => $question->label_en]),
                        ]);
                    }
                    if ($normalized !== null) {
                        FeedbackAnswer::query()->create([
                            'feedback_submission_id' => $submission->id,
                            'feedback_question_id' => $question->id,
                            'value' => $normalized,
                        ]);
                    }
                }
            }
        });

        FeedbackResponseDraft::query()
            ->where('student_id', $student->id)
            ->where('feedback_form_version_id', $version->id)
            ->delete();
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>|null
     */
    public function normalizeAnswer(FeedbackQuestion $question, mixed $payload): ?array
    {
        if ($payload === null || $payload === '') {
            return null;
        }

        return match ($question->type) {
            FeedbackQuestionType::Likert5 => $this->normalizeLikert($payload),
            FeedbackQuestionType::YesNo => $this->normalizeYesNo($payload),
            FeedbackQuestionType::MultipleChoice => $this->normalizeMc($question, $payload),
            FeedbackQuestionType::Text, FeedbackQuestionType::Note => $this->normalizeText($payload),
        };
    }

    protected function normalizeLikert(mixed $payload): ?array
    {
        $n = is_array($payload) ? ($payload['v'] ?? $payload['value'] ?? null) : $payload;
        $n = (int) $n;
        if ($n < 1 || $n > 5) {
            return null;
        }

        return ['v' => $n];
    }

    protected function normalizeYesNo(mixed $payload): ?array
    {
        $v = is_array($payload) ? ($payload['v'] ?? null) : $payload;
        if ($v === null || $v === '') {
            return null;
        }
        $b = filter_var($v, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($b === null) {
            $s = strtolower((string) $v);
            $b = match ($s) {
                'yes', '1', 'true' => true,
                'no', '0', 'false' => false,
                default => null,
            };
        }
        if ($b === null) {
            return null;
        }

        return ['v' => $b];
    }

    protected function normalizeMc(FeedbackQuestion $question, mixed $payload): ?array
    {
        $key = is_array($payload) ? ($payload['v'] ?? $payload['value'] ?? null) : $payload;
        $key = (string) $key;
        $opts = $question->options['choices'] ?? [];
        $allowed = collect($opts)->pluck('key')->all();
        if ($allowed && ! in_array($key, $allowed, true)) {
            return null;
        }

        return ['v' => $key];
    }

    protected function normalizeText(mixed $payload): ?array
    {
        $t = is_array($payload) ? ($payload['t'] ?? $payload['text'] ?? '') : (string) $payload;
        $t = trim((string) $t);

        return $t === '' ? null : ['t' => mb_substr($t, 0, 5000)];
    }

    public function saveDraft(Student $student, FeedbackFormVersion $version, array $staffSubjectIds, int $currentQuestionIndex, ?array $answers): void
    {
        FeedbackResponseDraft::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'feedback_form_version_id' => $version->id,
            ],
            [
                'staff_subject_ids' => array_values(array_map('intval', $staffSubjectIds)),
                'current_question_index' => $currentQuestionIndex,
                'answers' => $answers,
            ]
        );
    }
}
