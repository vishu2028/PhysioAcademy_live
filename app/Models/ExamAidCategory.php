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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materials()
    {
        return $this->hasMany(ExamAidMaterial::class);
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
