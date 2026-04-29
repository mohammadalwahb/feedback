<?php

namespace App\Models;

use App\Enums\FeedbackFormStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedbackForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title_en',
        'title_ku',
        'title_ar',
        'description_en',
        'description_ku',
        'description_ar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeedbackFormStatus::class,
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FeedbackFormVersion::class, 'feedback_form_id');
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $key = match ($locale) {
            'ku' => 'title_ku',
            'ar' => 'title_ar',
            default => 'title_en',
        };

        return $this->{$key} ?? $this->title_en;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $key = match ($locale) {
            'ku' => 'description_ku',
            'ar' => 'description_ar',
            default => 'description_en',
        };

        return $this->{$key} ?: $this->description_en;
    }

    public function currentSubmissionVersion(): ?FeedbackFormVersion
    {
        return $this->versions()
            ->where('accepts_submissions', true)
            ->whereNull('deleted_at')
            ->orderByDesc('version_number')
            ->first();
    }
}
