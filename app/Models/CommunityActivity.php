<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityActivity extends Model
{
    protected $fillable = [
    'title',
    'subject',
    'time',
];
}
