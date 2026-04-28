<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Identity
            'site_title' => 'My Website',
            'site_description' => 'A personal corner of the internet.',
            'footer_text' => '© '.date('Y').' My Website. All rights reserved.',

            // Appearance
            'background_color' => '#fdfbf7',
            'accent_color' => '#2c3e50',
            'font' => 'Instrument Sans',

            // Feed
            'posts_per_page' => '10',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
