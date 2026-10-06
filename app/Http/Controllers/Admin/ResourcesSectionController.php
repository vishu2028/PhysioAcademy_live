<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ResourcesSectionController extends Controller
{
    public const SETTING_KEY = 'resources_section_enabled';

    public function index()
    {
        $sectionEnabled = self::isEnabled();

        return view('admin.resources-section.index', compact('sectionEnabled'));
    }

    public function sectionToggle(Request $request)
    {
        Setting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => $request->boolean('section_enabled') ? '1' : '0',
                'group' => 'homepage',
                'label' => 'Resources section visible',
                'type' => 'boolean',
            ]
        );

        return back()->with('success', 'Resources section visibility updated successfully.');
    }

    public static function isEnabled(): bool
    {
        $value = Setting::where('key', self::SETTING_KEY)->value('value');

        return is_null($value) ? true : (bool) (int) $value;
    }
}
