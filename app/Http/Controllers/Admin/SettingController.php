<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $socialSettings = Setting::where('group', 'social')
            ->orderBy('label')
            ->get();

        return view('admin.settings.index', compact('socialSettings'));
    }

    public function update(Request $request)
    {
        $socialUrlRule = [
            'nullable',
            function ($attribute, $value, $fail) {
                $value = trim((string) $value);

                // Empty value or # is allowed
                if ($value === '' || $value === '#') {
                    return;
                }

                // Must be a valid URL
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    $fieldName = str_replace(['settings.', '_'], ['', ' '], $attribute);
                    $fail('The ' . ucfirst($fieldName) . ' must be a valid URL.');
                    return;
                }

                // Only allow http or https URLs
                if (! preg_match('/^https?:\/\//i', $value)) {
                    $fieldName = str_replace(['settings.', '_'], ['', ' '], $attribute);
                    $fail('The ' . ucfirst($fieldName) . ' must start with http:// or https://.');
                }
            },
        ];

        $request->validate([
            'settings.facebook_url' => $socialUrlRule,
            'settings.instagram_url' => $socialUrlRule,
            'settings.linkedin_url' => $socialUrlRule,
            'settings.youtube_url' => $socialUrlRule,
            'settings.contact_url' => $socialUrlRule,
            'settings.site_phone' => 'nullable|string|max:50',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|mimes:ico,png,jpg,jpeg,svg|max:1024',
            'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->has('settings')) {
            $settingsData = $request->settings;

            // Explicitly handle checkbox being unchecked
            $checkboxes = [
                'maintenance_mode',
                'enable_content_protection',
                'protection_disable_right_click',
                'protection_disable_devtools',
                'protection_disable_copy',
                'protection_disable_drag',
                'protection_enable_watermark',
            ];

            foreach ($checkboxes as $checkbox) {
                if (! isset($settingsData[$checkbox])) {
                    $settingsData[$checkbox] = '0';
                }
            }

            foreach ($settingsData as $key => $value) {
                if (is_string($value)) {
                    $value = trim($value);
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'label' => ucfirst(str_replace('_', ' ', $key)),
                        'type' => 'text',
                    ]
                );
            }
        }

        // Handle file uploads
        $fileFields = ['site_logo', 'site_favicon', 'footer_logo'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file
                $old = Setting::where('key', $field)->first();

                if ($old && $old->getRawOriginal('value')) {
                    Storage::disk('public')->delete($old->getRawOriginal('value'));
                }

                $path = $request->file($field)->store('settings', 'public');

                Setting::updateOrCreate(
                    ['key' => $field],
                    [
                        'value' => $path,
                        'label' => ucfirst(str_replace('_', ' ', $field)),
                        'type' => 'text',
                    ]
                );
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
