<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedbackFormVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'feedback_form_id',
        'version_number',
        'accepts_submissions',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'accepts_submissions' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FeedbackForm::class, 'feedback_form_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FeedbackQuestion::class, 'feedback_form_version_id')->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FeedbackSubmission::class, 'feedback_form_version_id');
    }

    public function isOpenForStudents(): bool
    {
        if (! $this->accepts_submissions) {
            return false;
        }
        $form = $this->form;
        if (! $form || $form->status !== \App\Enums\FeedbackFormStatus::Active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
