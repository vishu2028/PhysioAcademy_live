<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    protected $fillable = ['topic_id', 'title', 'type', 'content', 'file_path', 'url', 'order'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
