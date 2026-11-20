<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    protected $fillable = [
        'topic_id',
        'exam_aid_category_id',
        'title',
        'type',
        'content',
        'file_path',
        'url',
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

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
    public function category()
    {
        return $this->belongsTo(ExamAidCategory::class, 'exam_aid_category_id');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function isBookmarked()
    {
        if (!auth()->check()) return false;
        return $this->bookmarks()->where('user_id', auth()->id())->exists();
    }
}
