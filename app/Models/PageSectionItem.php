<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSectionItem extends Model
{
    protected $fillable = [
        'section_id', 'title', 'body', 'meta', 'order', 'enabled'
    ];

    protected $casts = [
        'meta' => 'array',
        'enabled' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(PageSection::class, 'section_id');
    }

    public function getTitleAttribute($value)
    {
        return brand_text($value);
    }

    public function getBodyAttribute($value)
    {
        return brand_text($value);
    }

    public function getMetaAttribute($value)
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

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = is_array($value) ? json_encode($value) : $value;
    }
}
