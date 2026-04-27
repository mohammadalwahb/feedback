<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'english_name',
        'kurdish_name',
        'arabic_name',
        'college_id',
        'department_id',
        'semester_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Staff the student may evaluate: same college, department, and semester (no per-student mapping).
     */
    public function evaluatableStaffSubjectsQuery(): Builder
    {
        return StaffSubject::query()
            ->where('college_id', $this->college_id)
            ->where('department_id', $this->department_id)
            ->where('semester_id', $this->semester_id)
            ->orderBy('instructor_name');
    }

    /**
     * @return list<int>
     */
    public function evaluatableStaffSubjectIds(): array
    {
        return $this->evaluatableStaffSubjectsQuery()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function feedbackSubmissions(): HasMany
    {
        return $this->hasMany(FeedbackSubmission::class);
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $key = match ($locale) {
            'ku' => 'kurdish_name',
            'ar' => 'arabic_name',
            default => 'english_name',
        };

        return $this->{$key} ?? $this->english_name;
    }
}
