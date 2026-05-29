<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['client_name', 'client_designation', 'content', 'rating', 'client_image', 'status', 'order'];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getContentAttribute($value)
    {
        return brand_text($value);
    }
}
