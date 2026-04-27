<?php

namespace App\Models;

use App\Enums\FeedbackQuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedbackQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'feedback_form_version_id',
        'type',
        'label_en',
        'label_ku',
        'label_ar',
        'is_required',
        'sort_order',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'type' => FeedbackQuestionType::class,
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(FeedbackFormVersion::class, 'feedback_form_version_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackAnswer::class, 'feedback_question_id');
    }

    public function localizedLabel(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $key = match ($locale) {
            'ku' => 'label_ku',
            'ar' => 'label_ar',
            default => 'label_en',
        };

        return $this->{$key} ?? $this->label_en;
    }
}
