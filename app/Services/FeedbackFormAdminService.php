<?php

namespace App\Services;

use App\Enums\FeedbackFormStatus;
use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackForm;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponseDraft;
use App\Models\FeedbackSubmission;
use Illuminate\Support\Facades\DB;

class FeedbackFormAdminService
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    public function createForm(string $titleEn, ?string $titleKu, ?string $titleAr, \App\Models\User $admin): FeedbackForm
    {
        $form = FeedbackForm::query()->create([
            'title_en' => $titleEn,
            'title_ku' => $titleKu,
            'title_ar' => $titleAr,
            'status' => FeedbackFormStatus::Draft,
        ]);

        $this->ensureInitialVersion($form);
        $this->auditLogger->log($admin, 'feedback_form.created', $form);

        return $form;
    }

    public function ensureInitialVersion(FeedbackForm $form): FeedbackFormVersion
    {
        $exists = $form->versions()->exists();
        if ($exists) {
            return $form->versions()->orderByDesc('version_number')->first();
        }

        return $form->versions()->create([
            'version_number' => 1,
            'accepts_submissions' => false,
        ]);
    }

    public function publishNewVersion(FeedbackForm $form, \App\Models\User $admin): FeedbackFormVersion
    {
        return DB::transaction(function () use ($form, $admin) {
            $latest = $form->versions()->orderByDesc('version_number')->first();
            $next = ($latest?->version_number ?? 0) + 1;

            $version = $form->versions()->create([
                'version_number' => $next,
                'accepts_submissions' => false,
            ]);

            if ($latest) {
                foreach ($latest->questions()->orderBy('sort_order')->get() as $q) {
                    FeedbackQuestion::query()->create([
                        'feedback_form_version_id' => $version->id,
                        'type' => $q->type,
                        'label_en' => $q->label_en,
                        'label_ku' => $q->label_ku,
                        'label_ar' => $q->label_ar,
                        'is_required' => $q->is_required,
                        'sort_order' => $q->sort_order,
                        'options' => $q->options,
                    ]);
                }
            }

            $this->auditLogger->log($admin, 'feedback_form.version_created', $version, ['number' => $next]);

            return $version;
        });
    }

    public function upsertQuestion(
        FeedbackFormVersion $version,
        array $data,
        ?FeedbackQuestion $question,
        \App\Models\User $admin
    ): FeedbackQuestion {
        $payload = [
            'type' => FeedbackQuestionType::from($data['type'])->value,
            'label_en' => $data['label_en'],
            'label_ku' => $data['label_ku'] ?? null,
            'label_ar' => $data['label_ar'] ?? null,
            'is_required' => (bool) ($data['is_required'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'options' => $data['options'] ?? null,
        ];

        if ($question) {
            $question->fill($payload);
            $question->save();
            $this->auditLogger->log($admin, 'feedback_question.updated', $question);
        } else {
            $question = $version->questions()->create($payload);
            $this->auditLogger->log($admin, 'feedback_question.created', $question);
        }

        return $question;
    }

    public function reorderQuestions(FeedbackFormVersion $version, array $orderedIds, \App\Models\User $admin): void
    {
        foreach ($orderedIds as $index => $id) {
            FeedbackQuestion::query()->where('feedback_form_version_id', $version->id)
                ->where('id', $id)
                ->update(['sort_order' => $index]);
        }
        $this->auditLogger->log($admin, 'feedback_question.reordered', $version, ['order' => $orderedIds]);
    }

    public function setFormStatus(FeedbackForm $form, FeedbackFormStatus $status, \App\Models\User $admin): void
    {
        $form->status = $status;
        $form->save();
        $this->auditLogger->log($admin, 'feedback_form.status', $form, ['status' => $status->value]);
    }

    public function configureVersionWindow(
        FeedbackFormVersion $version,
        bool $accepts,
        ?string $startsAt,
        ?string $endsAt,
        \App\Models\User $admin
    ): void {
        $version->accepts_submissions = $accepts;
        $version->starts_at = $startsAt;
        $version->ends_at = $endsAt;
        $version->save();

        if ($accepts) {
            FeedbackFormVersion::query()
                ->where('feedback_form_id', $version->feedback_form_id)
                ->where('id', '!=', $version->id)
                ->update(['accepts_submissions' => false]);
        }

        $this->auditLogger->log($admin, 'feedback_form.version_window', $version, [
            'accepts' => $accepts,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    public function deleteForm(FeedbackForm $form, \App\Models\User $admin): void
    {
        DB::transaction(function () use ($form, $admin) {
            $form->load('versions.questions');
            $versionIds = $form->versions->pluck('id');
            if ($versionIds->isNotEmpty()) {
                FeedbackResponseDraft::query()->whereIn('feedback_form_version_id', $versionIds)->delete();
            }
            foreach ($form->versions as $version) {
                foreach ($version->questions as $question) {
                    $question->delete();
                }
                $version->delete();
            }
            $form->delete();
            $this->auditLogger->log($admin, 'feedback_form.deleted', $form);
        });
    }

    public function deleteFormResponses(FeedbackForm $form, \App\Models\User $admin): int
    {
        return DB::transaction(function () use ($form, $admin) {
            $versionIds = $form->versions()->pluck('id');
            if ($versionIds->isEmpty()) {
                return 0;
            }

            FeedbackResponseDraft::query()
                ->whereIn('feedback_form_version_id', $versionIds)
                ->delete();

            $deleted = FeedbackSubmission::query()
                ->whereIn('feedback_form_version_id', $versionIds)
                ->delete();

            $this->auditLogger->log($admin, 'feedback_form.responses_deleted', $form, [
                'deleted_submissions' => $deleted,
            ]);

            return (int) $deleted;
        });
    }
}
