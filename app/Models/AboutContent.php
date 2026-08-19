<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutContent extends Model
{
    protected $fillable = [
        'main_title',
        'main_description',
        'topic_count',
        'question_count',
        'student_count',
    ];
}
