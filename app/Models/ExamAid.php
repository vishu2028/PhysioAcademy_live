<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAid extends Model
{
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
