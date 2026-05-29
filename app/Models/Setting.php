<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label', 'type'];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return brand_text($setting ? $setting->value : $default);
    }

    public function getValueAttribute($value)
    {
        return brand_text($value);
    }
}
