<?php

if (!function_exists('get_setting')) {
    /**
     * Get setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_setting($key, $default = null)
    {
        try {
            $setting = \App\Models\Setting::where('key', $key)->first();
            return brand_text($setting ? $setting->value : $default);
        } catch (\Exception $e) {
            return brand_text($default);
        }
    }
}

if (!function_exists('brand_text')) {
    /**
     * Normalize legacy project branding at render time without changing CMS records.
     *
     * @param mixed $value
     * @return mixed
     */
    function brand_text($value)
    {
        if (is_array($value)) {
            return array_map('brand_text', $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        return str_replace(
            [
                'dynamic-laravel-cms',
                'Dynamic Laravel CMS',
                'dynamic laravel cms',
                'PhysioAcademy',
                'Physiocademy',
                'PhysiocAademy',
            ],
            'Physio Academy',
            $value
        );
    }
}
