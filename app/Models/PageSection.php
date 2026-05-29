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

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function items()
    {
        return $this->hasMany(PageSectionItem::class, 'section_id')->orderBy('order');
    }

    public function getContentAttribute($value)
    {
        if ($value === null || $value === '') {
            return brand_text($value);
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return brand_text($decoded);
        }

        return brand_text($value);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = is_array($value) ? json_encode($value) : $value;
    }
}
