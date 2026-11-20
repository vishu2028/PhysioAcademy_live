<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'subject_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
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
        'is_active' => 'boolean',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
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
