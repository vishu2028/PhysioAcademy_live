<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['name', 'academic_year_id', 'order'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }
}
