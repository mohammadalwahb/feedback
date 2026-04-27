<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackAnswer extends Model
{
    protected $fillable = [
        'feedback_submission_id',
        'feedback_question_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FeedbackSubmission::class, 'feedback_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FeedbackQuestion::class, 'feedback_question_id');
    }
}
