<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAidMaterial extends Model
{
    protected $fillable = [
        'exam_aid_id',
        'exam_aid_category_id',
        'title',
        'type',
        'file_path',
        'url',
        'content',
        'order',
        'seo_title',
        'meta_description',
        'meta_keywords',
        'seo_slug',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'robots_index',
        'robots_follow',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function examAid()
    {
        return $this->belongsTo(ExamAid::class);
    }

    public function category()
    {
        return $this->belongsTo(ExamAidCategory::class, 'exam_aid_category_id');
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
