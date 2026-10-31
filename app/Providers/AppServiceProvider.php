<?php

namespace App\Providers;

use App\Support\SiteOptions;
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
            config(SiteOptions::toConfig());
        } catch (\Exception) {
            // Table not yet migrated — use defaults
        }
    }
}
