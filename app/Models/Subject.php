<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'icon',
        'image',
        'status',
        'order',
        'seo_title',
        'meta_description',
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
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function units()
    {
        return $this->hasMany(Unit::class);
    }
    public function unitTopics()
    {
        return $this->hasMany(UnitTopic::class);
    }
    public function parentTopics()
    {
        return $this->hasMany(ParentTopic::class);
    }
}
