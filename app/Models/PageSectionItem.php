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
        'is_protected' => 'boolean',
    ];

//    public function getMetaAttribute($value)
//    {
//        return brand_text($value);
//    }
    public function getMetaAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $decoded = is_array($value)
            ? $value
            : json_decode($value, true);

        return brand_text($decoded ?? $value);
    }

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
}
