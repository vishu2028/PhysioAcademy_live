<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Page extends Model
{
    use Loggable;

    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function getTitleAttribute($value)
    {
        return brand_text($value);
    }

    public function getContentAttribute($value)
    {
        return brand_text($value);
    }

    public function getMetaTitleAttribute($value)
    {
        return brand_text($value);
    }

    public function getMetaDescriptionAttribute($value)
    {
        return brand_text($value);
    }
}
