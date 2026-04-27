<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'english_name',
        'kurdish_name',
        'arabic_name',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
