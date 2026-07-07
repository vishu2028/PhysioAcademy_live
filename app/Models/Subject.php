<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = ['name',  'code', 'slug', 'description', 'icon', 'image', 'status', 'order'];

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
