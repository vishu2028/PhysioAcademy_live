<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page_id', 'name', 'slug', 'type', 'content', 'order', 'enabled'
    ];

    protected $casts = [
        'content' => 'array',
        'enabled' => 'boolean',
    ];

    public function getContentAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        // Decode JSON if still a string (accessor fires before cast)
        $decoded = is_array($value) ? $value : json_decode($value, true);
        return brand_text($decoded ?? $value);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function items()
    {
        return $this->hasMany(PageSectionItem::class, 'section_id')->orderBy('order');
    }
}
