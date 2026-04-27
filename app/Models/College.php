<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class College extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_en',
        'name_ku',
        'name_ar',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
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
