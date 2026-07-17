<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'button_text',
        'button_url',
        'image_path',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            true
        );
    }
}
