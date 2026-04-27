<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffSubject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_employee_id',
        'instructor_name',
        'subject_name',
        'college_id',
        'department_id',
        'semester_id',
    ];

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

    public function feedbackSubmissions(): HasMany
    {
        return $this->hasMany(FeedbackSubmission::class);
    }
}
