<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    protected $fillable = ['topic_id', 'exam_aid_category_id', 'title', 'type', 'content', 'file_path', 'url', 'order'];

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
