<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAidMaterial extends Model
{
    protected $fillable = [
        'exam_aid_id',
        'title',
        'type',
        'file_path',
        'url',
        'content',
        'order',
    ];

    public function examAid()
    {
        return $this->belongsTo(ExamAid::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
