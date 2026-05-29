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
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
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
                'protection_enable_watermark'
            ];
            foreach ($checkboxes as $checkbox) {
                if (!isset($settingsData[$checkbox])) {
                    $settingsData[$checkbox] = '0';
                }
            }

            foreach ($settingsData as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'label' => ucfirst(str_replace('_', ' ', $key))]
                );
            }
        }

        // Handle file uploads
        $fileFields = ['site_logo', 'site_favicon', 'footer_logo'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file
                $old = Setting::where('key', $field)->first();
                if ($old && $old->value) {
                    Storage::disk('public')->delete($old->value);
                }

                $path = $request->file($field)->store('settings', 'public');
                Setting::updateOrCreate(
                    ['key' => $field],
                    ['value' => $path, 'label' => ucfirst(str_replace('_', ' ', $field))]
                );
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
