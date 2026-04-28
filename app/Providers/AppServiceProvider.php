<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Load settings from DB into app config (gracefully skip if table doesn't exist yet)
        try {
            config([
                'app.name' => Setting::get('site_title', config('app.name')),
                'site.description' => Setting::get('site_description', ''),
                'site.footer_text' => Setting::get('footer_text', ''),
                'site.background_color' => Setting::get('background_color', '#fdfbf7'),
                'site.accent_color' => Setting::get('accent_color', '#2c3e50'),
                'site.font' => Setting::get('font', 'Instrument Sans'),
                'site.posts_per_page' => (int) Setting::get('posts_per_page', 10),
            ]);
        } catch (\Exception) {
            // Table not yet migrated — use defaults
        }
    }
}
