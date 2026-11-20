<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAid extends Model
{
    public const DESCRIPTION_LIMIT = 120;

    protected $fillable = [
        'subject_id',
        'unit_id',
        'academic_year_id',
        'semester_id',
        'title',
        'description',
        'viva_question',
        'exam_question',
        'status',
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
        'status' => 'boolean',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
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
        return $this->hasMany(ExamAidMaterial::class)->orderBy('order');
    }
}
