<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAidCategory extends Model
{
    protected $fillable = [
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

    public function materials()
    {
        return $this->hasMany(ExamAidMaterial::class);
    }

    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function getPublicUrlAttribute()
    {
        return route('exam-aid.category', $this->slug);
    }
}
