<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'bottom_line',
        'subject_id',
        'parent_topic_id',
        'unit_topic_id',
        'academic_year_id',
        'semester_id',
        'parent_id',
        'module_number',
        'status',
        'order',
        'is_protected',
    ];

    public function parentTopic()
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function subtopics()
    {
        return $this->hasMany(Topic::class, 'parent_id')
            ->orderBy('order');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function materials()
    {
        return $this->hasMany(LearningMaterial::class)
            ->orderBy('order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('topics.status', true);
    }

    /**
     * Topics that should be visible and counted in the curriculum.
     */
    public function scopeCurriculumVisible(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereNull('topics.parent_id')
            ->whereNotNull('topics.academic_year_id')
            ->whereHas('subject', function (Builder $subjectQuery) {
                $subjectQuery->where('status', true);
            })
            ->whereHas('unitTopic', function (Builder $unitTopicQuery) {
                $unitTopicQuery
                    ->where('status', true)
                    ->whereHas('unit', function (Builder $unitQuery) {
                        $unitQuery->where('is_active', true);
                    });
            });
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function isBookmarked()
    {
        if (! auth()->check()) {
            return false;
        }

        return $this->bookmarks()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitTopic()
    {
        return $this->belongsTo(
            UnitTopic::class,
            'unit_topic_id'
        );
    }

    public function parentTopicGroup()
    {
        return $this->belongsTo(
            ParentTopic::class,
            'parent_topic_id'
        );
    }
}
