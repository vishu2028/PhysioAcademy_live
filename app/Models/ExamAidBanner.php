<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAidBanner extends Model
{
     protected $fillable = [
        'main_title',
        'description',
        'title_1',
        'title_2',
        'heading_1',
        'percentage_value',
    ];
}
