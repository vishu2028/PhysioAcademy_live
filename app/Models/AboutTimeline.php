<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutTimeline extends Model
{
      protected $fillable = [
        'year',
        'title',
        'description',
    ];
}
