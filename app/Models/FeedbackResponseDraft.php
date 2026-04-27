<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackResponseDraft extends Model
{
    protected $fillable = [
        'student_id',
        'feedback_form_version_id',
        'staff_subject_ids',
        'current_question_index',
        'answers',
    ];

    protected function casts(): array
    {
        return [
            'staff_subject_ids' => 'array',
            'answers' => 'array',
            'current_question_index' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(FeedbackFormVersion::class, 'feedback_form_version_id');
    }
}
