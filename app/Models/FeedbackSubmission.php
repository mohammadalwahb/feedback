<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackSubmission extends Model
{
    protected $fillable = [
        'student_id',
        'staff_subject_id',
        'feedback_form_version_id',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function staffSubject(): BelongsTo
    {
        return $this->belongsTo(StaffSubject::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(FeedbackFormVersion::class, 'feedback_form_version_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackAnswer::class, 'feedback_submission_id');
    }
}
