<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPrepCard extends Model
{
    /**
     * Icons available in the front-end icon set (public/ui-physio/style.css).
     */
    public const ICONS = [
        'beaker', 'bone', 'book', 'brain', 'briefcase', 'calendar', 'check', 'clipboard',
        'clock', 'dna', 'edit', 'file', 'flame', 'folder', 'graduation', 'heart-pulse',
        'help', 'hospital', 'lightbulb', 'mail', 'map', 'megaphone', 'message', 'mic',
        'microscope', 'puzzle', 'refresh', 'rocket', 'search', 'settings', 'trending',
        'users', 'zap',
    ];

    protected $fillable = [
        'title',
        'description',
        'icon',
        'button_text',
        'exam_aid_category_id',
        'custom_url',
        'is_large',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_large' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExamAidCategory::class, 'exam_aid_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Where the card button goes: custom URL, else its category page, else the Exam Aid page.
     */
    public function getLinkUrlAttribute(): string
    {
        if ($this->custom_url) {
            return $this->custom_url;
        }

        if ($this->category && $this->category->is_active) {
            return route('exam-aid.category', $this->category->slug);
        }

        return route('exam-aid');
    }
}
