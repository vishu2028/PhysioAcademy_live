<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitTopic extends Model
{
    protected $fillable = [
        'subject_id',
        'unit_id',
        'title',
        'slug',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function lmsTopics()
    {
        return $this->hasMany(Topic::class, 'unit_topic_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
