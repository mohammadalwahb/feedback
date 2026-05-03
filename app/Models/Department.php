<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'college_id',
        'name_en',
        'name_ku',
        'name_ar',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $key = match ($locale) {
            'ku' => 'name_ku',
            'ar' => 'name_ar',
            default => 'name_en',
        };

        return $this->{$key} ?? $this->name_en;
    }
}
