<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Topic;
use App\Models\Page;

class ProtectionSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'enable_content_protection' => '1',
            'protection_disable_right_click' => '1',
            'protection_disable_devtools' => '1',
            'protection_disable_copy' => '1',
            'protection_disable_drag' => '1',
            'protection_enable_watermark' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'label' => ucfirst(str_replace('_', ' ', $key)), 'group' => 'protection']
            );
        }

        // Ensure at least one topic is protected for testing
        Topic::where('id', '>', 0)->update(['is_protected' => '1']);
        Page::where('id', '>', 0)->update(['is_protected' => '1']);
        
        echo "Protection settings enabled and all topics/pages marked as protected.\n";
    }
}
