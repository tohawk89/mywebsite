<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Site settings edited in the admin (Site settings pages): their defaults and how they map onto
 * config('site.*') at boot. Background options live separately in SiteBackground.
 */
class SiteOptions
{
    public const FONTS = [
        'Instrument Sans',
        'Inter',
        'Merriweather',
        'Playfair Display',
        'Roboto Mono',
        'Lora',
    ];

    public const ANALYTICS_PROVIDERS = [
        'none' => 'None',
        'ga4' => 'Google Analytics 4',
        'plausible' => 'Plausible',
    ];

    /**
     * Setting key => default value.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        // General
        'site_title' => 'My Website',
        'site_description' => '',
        'footer_text' => '',
        'site_icon' => '',
        // Appearance
        'background_color' => '#fdfbf7',
        'accent_color' => '#2c3e50',
        'font' => 'Instrument Sans',
        'heading_font' => '',
        'card_corners' => 'square',
        'card_shadow' => '1',
        'max_columns' => '4',
        // Feed
        'posts_per_page' => '10',
        'feed_loading' => 'infinite',
        'show_dates' => '0',
        'hidden_types' => '[]',
        // SEO and sharing
        'seo_share_image' => '',
        'seo_twitter_handle' => '',
        'seo_noindex' => '0',
        'seo_google_verification' => '',
        // Integrations
        'analytics_provider' => 'none',
        'analytics_ga_id' => '',
        'analytics_plausible_domain' => '',
        'analytics_exclude_admins' => '1',
        'github_token' => '',
        // Maintenance
        'maintenance_enabled' => '0',
        'maintenance_message' => '',
    ];

    public static function get(string $key): string
    {
        return (string) Setting::get($key, self::DEFAULTS[$key] ?? '');
    }

    public static function flag(string $key): bool
    {
        return self::get($key) === '1';
    }

    /**
     * @return array<int, string>
     */
    public static function hiddenTypes(): array
    {
        $types = json_decode(self::get('hidden_types'), true);

        return is_array($types) ? array_values(array_filter($types, 'is_string')) : [];
    }

    public static function fileUrl(string $key): ?string
    {
        $path = self::get($key);

        return $path !== '' ? Storage::disk(SiteBackground::disk())->url($path) : null;
    }

    /**
     * The decrypted GitHub token, or null when none is set (or it can't be decrypted).
     */
    public static function githubToken(): ?string
    {
        $encrypted = self::get('github_token');

        if ($encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Values for config(), applied in AppServiceProvider.
     *
     * @return array<string, mixed>
     */
    public static function toConfig(): array
    {
        return [
            'app.name' => Setting::get('site_title') ?: config('app.name'),
            'site.description' => self::get('site_description'),
            'site.footer_text' => self::get('footer_text'),
            'site.icon_url' => self::fileUrl('site_icon'),
            'site.background_color' => self::get('background_color'),
            'site.accent_color' => self::get('accent_color'),
            'site.font' => self::get('font'),
            'site.heading_font' => self::get('heading_font'),
            'site.card_corners' => self::get('card_corners'),
            'site.card_shadow' => self::flag('card_shadow'),
            'site.max_columns' => (int) self::get('max_columns'),
            'site.posts_per_page' => (int) self::get('posts_per_page'),
            'site.feed_loading' => self::get('feed_loading'),
            'site.show_dates' => self::flag('show_dates'),
            'site.hidden_types' => self::hiddenTypes(),
            'site.seo.share_image' => self::fileUrl('seo_share_image'),
            'site.seo.twitter_handle' => self::get('seo_twitter_handle'),
            'site.seo.noindex' => self::flag('seo_noindex'),
            'site.seo.google_verification' => self::get('seo_google_verification'),
            'site.analytics.provider' => self::get('analytics_provider'),
            'site.analytics.ga_id' => self::get('analytics_ga_id'),
            'site.analytics.plausible_domain' => self::get('analytics_plausible_domain'),
            'site.analytics.exclude_admins' => self::flag('analytics_exclude_admins'),
            'site.maintenance.enabled' => self::flag('maintenance_enabled'),
            'site.maintenance.message' => self::get('maintenance_message'),
            'site.background' => SiteBackground::load(),
        ];
    }
}
