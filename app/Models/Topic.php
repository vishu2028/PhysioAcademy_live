<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'title', 'slug', 'description', 'subject_id', 'academic_year_id', 
        'semester_id', 'parent_id', 'module_number', 'status', 'order', 'is_protected'
    ];

    public function parentTopic()
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function subtopics()
    {
        return $this->hasMany(Topic::class, 'parent_id')->orderBy('order');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function materials()
    {
        return $this->hasMany(LearningMaterial::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
