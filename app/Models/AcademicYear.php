<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = ['name', 'slug', 'description', 'units_count', 'topics_count', 'status', 'order'];

    public function semesters()
    {
        return $this->hasMany(Semester::class)->orderBy('order');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
