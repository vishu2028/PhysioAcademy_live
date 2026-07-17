<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
                    $fieldName = str_replace(
                        ['settings.', '_'],
                        ['', ' '],
                        $attribute
                    );

                    $fail(
                        'The ' . ucfirst($fieldName) .
                        ' must be a valid URL.'
                    );

                    return;
                }

                // Only allow http or https URLs
                if (! preg_match('/^https?:\/\//i', $value)) {
                    $fieldName = str_replace(
                        ['settings.', '_'],
                        ['', ' '],
                        $attribute
                    );

                    $fail(
                        'The ' . ucfirst($fieldName) .
                        ' must start with http:// or https://.'
                    );
                }
            },
        ];

        $request->validate([
            'settings.facebook_url' => $socialUrlRule,
            'settings.instagram_url' => $socialUrlRule,
            'settings.linkedin_url' => $socialUrlRule,
            'settings.youtube_url' => $socialUrlRule,
            'settings.contact_url' => $socialUrlRule,

            'settings.site_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            /*
             * One-on-One Doubt Session settings
             */
            'settings.doubt_session_enabled' => [
                'required',
                'in:0,1',
            ],

            'settings.doubt_session_is_free' => [
                'required',
                'in:0,1',
            ],

            'settings.doubt_session_price' => [
                Rule::requiredIf(function () use ($request) {
                    $enabled = (string) $request->input(
                            'settings.doubt_session_enabled',
                            '0'
                        ) === '1';

                    $isFree = (string) $request->input(
                            'settings.doubt_session_is_free',
                            '0'
                        ) === '1';

                    return $enabled && ! $isFree;
                }),
                'nullable',
                'numeric',
                'min:1',
                'max:100000',
            ],

            'settings.doubt_session_duration_minutes' => [
                Rule::requiredIf(function () use ($request) {
                    return (string) $request->input(
                            'settings.doubt_session_enabled',
                            '0'
                        ) === '1';
                }),
                'nullable',
                'integer',
                'min:15',
                'max:480',
            ],

            'site_logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],

            'site_favicon' => [
                'nullable',
                'mimes:ico,png,jpg,jpeg,svg',
                'max:1024',
            ],

            'footer_logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
        ]);

        if ($request->has('settings')) {
            $settingsData = $request->settings;

            // Explicitly handle unchecked checkboxes
            $checkboxes = [
                'maintenance_mode',
                'enable_content_protection',
                'protection_disable_right_click',
                'protection_disable_devtools',
                'protection_disable_copy',
                'protection_disable_drag',
                'protection_enable_watermark',

                // Doubt session checkboxes
                'doubt_session_enabled',
                'doubt_session_is_free',
            ];

            foreach ($checkboxes as $checkbox) {
                if (! isset($settingsData[$checkbox])) {
                    $settingsData[$checkbox] = '0';
                }
            }

            /*
             * Free sessions do not require Razorpay payment.
             * Store their price as zero.
             */
            if (
                (string) ($settingsData['doubt_session_is_free'] ?? '0')
                === '1'
            ) {
                $settingsData['doubt_session_price'] = '0';
            }

            $doubtSessionSettingTypes = [
                'doubt_session_enabled' => 'boolean',
                'doubt_session_is_free' => 'boolean',
                'doubt_session_price' => 'number',
                'doubt_session_duration_minutes' => 'number',
            ];

            foreach ($settingsData as $key => $value) {
                if (is_string($value)) {
                    $value = trim($value);
                }

                $settingPayload = [
                    'value' => $value,
                    'label' => ucfirst(
                        str_replace('_', ' ', $key)
                    ),
                    'type' => $doubtSessionSettingTypes[$key] ?? 'text',
                ];

                /*
                 * Keep doubt-session settings grouped together.
                 */
                if (array_key_exists($key, $doubtSessionSettingTypes)) {
                    $settingPayload['group'] = 'doubt_session';
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    $settingPayload
                );
            }
        }

        // Handle file uploads
        $fileFields = [
            'site_logo',
            'site_favicon',
            'footer_logo',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file
                $old = Setting::where('key', $field)->first();

                if ($old && $old->getRawOriginal('value')) {
                    Storage::disk('public')->delete(
                        $old->getRawOriginal('value')
                    );
                }

                $path = $request->file($field)
                    ->store('settings', 'public');

                Setting::updateOrCreate(
                    ['key' => $field],
                    [
                        'value' => $path,
                        'label' => ucfirst(
                            str_replace('_', ' ', $field)
                        ),
                        'type' => 'text',
                    ]
                );
            }
        }

        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }
}
