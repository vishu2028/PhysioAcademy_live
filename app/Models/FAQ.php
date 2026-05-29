<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = 'faqs';

    protected $fillable = ['question', 'answer', 'category', 'order', 'status'];

    protected $casts = [
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

    public function getQuestionAttribute($value)
    {
        return brand_text($value);
    }

    public function getAnswerAttribute($value)
    {
        return brand_text($value);
    }
}
