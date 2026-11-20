<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Page extends Model
{
    use Loggable;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'status',
        'is_protected',
        'seo_title',
        'meta_keywords',
        'seo_slug',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'robots_index',
        'robots_follow',
    ];

    protected $casts = [
        'status' => 'boolean',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
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

    public function sections()
    {
        return $this->hasMany(PageSection::class)->where('enabled', true)->orderBy('order');
    }

    public function getUrlAttribute()
    {
        return match ($this->slug) {
            'about-us' => route('about'),
            'contact-us' => route('contact'),
            'bookmarks' => route('bookmarks'),
            'exam-aid' => route('exam-aid'),
            'home' => route('home'),
            default => route('page.show', $this->slug),
        };
    }
}
