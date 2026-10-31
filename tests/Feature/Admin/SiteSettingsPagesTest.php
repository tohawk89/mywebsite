<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SiteSettings;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\PostTypes\Repository\Handler;
use App\Support\SiteBackground;
use App\Support\SiteOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteSettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * Save settings through the component.
     *
     * @param  array<string, mixed>  $values
     */
    private function saveSettings(array $values, string $section = 'general'): Testable
    {
        $component = Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => $section]);

        foreach ($values as $key => $value) {
            $component->set($key, $value);
        }

        return $component->call('save');
    }

    /**
     * Settings are applied to config at boot; mirror that, then load the home page as a guest.
     */
    private function home(): TestResponse
    {
        config(SiteOptions::toConfig());
        auth()->logout();

        return $this->get('/');
    }

    public static function sections(): array
    {
        return array_map(fn ($key) => [$key], array_keys(SiteSettings::SECTIONS));
    }

    #[DataProvider('sections')]
    public function test_each_settings_page_renders_with_sidebar_link(string $section): void
    {
        $this->actingAs($this->admin())
            ->get(route('settings', $section))
            ->assertOk()
            ->assertSee(SiteSettings::SECTIONS[$section][0])
            ->assertSee('href="'.route('settings', $section).'"', false);
    }

    public function test_unknown_settings_page_is_not_found(): void
    {
        $this->actingAs($this->admin())->get('/admin/settings/nope')->assertNotFound();
    }

    public function test_appearance_settings_reach_the_public_site(): void
    {
        $this->saveSettings([
            'accent_color' => '#ff5500',
            'heading_font' => 'Playfair Display',
            'card_corners' => 'rounded',
            'card_shadow' => false,
            'max_columns' => '3',
        ], 'appearance')->assertHasNoErrors();
        Post::factory()->create(['posted_at' => now()->subMinute()]);

        $this->home()
            ->assertSee('--site-accent: #ff5500;', false)
            ->assertSee("--site-heading-font: 'Playfair Display'", false)
            ->assertSee('playfair-display:400', false)
            ->assertSee('class="cards-rounded cards-flat"', false)
            ->assertSee('feed-grid is-max-3', false);
    }

    public function test_hidden_post_types_are_left_off_the_feed(): void
    {
        Post::factory()->create(['type' => 'quote', 'content' => 'Hidden quote', 'posted_at' => now()->subMinute()]);
        Post::factory()->create(['title' => 'Visible blog', 'posted_at' => now()->subMinute()]);

        $this->saveSettings([], 'feed')->call('toggleHiddenType', 'quote')->call('save')->assertHasNoErrors();

        $this->assertSame(['quote'], SiteOptions::hiddenTypes());
        $this->home()->assertSee('Visible blog')->assertDontSee('Hidden quote');
    }

    public function test_feed_can_load_with_a_button_and_show_dates(): void
    {
        Post::factory()->count(3)->create(['posted_at' => now()->subDay()]);

        $this->saveSettings(['posts_per_page' => 2, 'feed_loading' => 'button', 'show_dates' => true], 'feed')->assertHasNoErrors();

        $this->home()
            ->assertSee('feed-load-more', false)
            ->assertDontSee('x-intersect', false)
            ->assertSee('<time class="card-date', false)
            ->assertSee(now()->subDay()->format('M j, Y'));
    }

    public function test_seo_settings_render_meta_tags(): void
    {
        Storage::fake(SiteBackground::disk());

        $this->saveSettings([
            'site_description' => 'A personal corner of the internet.',
            'seo_twitter_handle' => '@nazar',
            'seo_noindex' => true,
            'seo_google_verification' => 'abcDEF123_verify',
            'share_image_upload' => UploadedFile::fake()->image('share.jpg', 1200, 630),
        ], 'seo')->assertHasNoErrors();

        $this->assertSame('nazar', Setting::get('seo_twitter_handle'));

        $this->home()
            ->assertSee('<meta name="description" content="A personal corner of the internet.">', false)
            ->assertSee('<meta property="og:description" content="A personal corner of the internet.">', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<meta name="google-site-verification" content="abcDEF123_verify">', false)
            ->assertSee('<meta name="twitter:site" content="@nazar">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('property="og:image"', false);
    }

    public function test_seo_inputs_are_validated(): void
    {
        $this->saveSettings(['seo_twitter_handle' => 'not a handle!', 'seo_google_verification' => '<meta>'], 'seo')
            ->assertHasErrors(['seo_twitter_handle', 'seo_google_verification']);
    }

    public function test_google_analytics_loads_for_visitors_but_not_admins(): void
    {
        $this->saveSettings(['analytics_provider' => 'ga4', 'analytics_ga_id' => 'G-ABC123XYZ'], 'integrations')->assertHasNoErrors();

        $this->home()
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123XYZ', false)
            ->assertDontSee('plausible.io', false);

        $this->actingAs($this->admin())->get('/')->assertDontSee('googletagmanager', false);
    }

    public function test_plausible_loads_with_its_domain(): void
    {
        $this->saveSettings([
            'analytics_provider' => 'plausible',
            'analytics_plausible_domain' => 'Example.COM',
            'analytics_exclude_admins' => false,
        ], 'integrations')->assertHasNoErrors();

        $this->home()->assertSee('<script defer data-domain="example.com" src="https://plausible.io/js/script.js"></script>', false);
    }

    public function test_analytics_ids_are_validated(): void
    {
        $this->saveSettings(['analytics_provider' => 'ga4', 'analytics_ga_id' => 'UA-12345-1'], 'integrations')
            ->assertHasErrors(['analytics_ga_id']);

        $this->saveSettings(['analytics_provider' => 'ga4', 'analytics_ga_id' => ''], 'integrations')
            ->assertHasErrors(['analytics_ga_id']);

        $this->saveSettings(['analytics_provider' => 'plausible', 'analytics_plausible_domain' => 'not a domain'], 'integrations')
            ->assertHasErrors(['analytics_plausible_domain']);
    }

    public function test_no_analytics_by_default(): void
    {
        $this->get('/')->assertDontSee('googletagmanager', false)->assertDontSee('plausible.io', false);
    }

    public function test_github_token_is_stored_encrypted_and_used_for_repository_lookups(): void
    {
        $component = $this->saveSettings(['github_token_input' => 'github_pat_secret123'], 'integrations')
            ->assertHasNoErrors()
            ->assertSet('github_token_input', '')
            ->assertSet('has_github_token', true)
            ->assertDontSee('github_pat_secret123');

        $this->assertNotSame('github_pat_secret123', Setting::get('github_token'));
        $this->assertSame('github_pat_secret123', SiteOptions::githubToken());

        // Saving again with the field blank keeps the token.
        $component->call('save');
        $this->assertSame('github_pat_secret123', SiteOptions::githubToken());

        Http::fake(['api.github.com/*' => Http::response(['full_name' => 'laravel/framework'])]);
        (new Handler)->buildMetaData(['repo_url' => 'https://github.com/laravel/framework']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer github_pat_secret123'));

        $component->call('removeGithubToken')->assertSet('has_github_token', false);
        $this->assertNull(SiteOptions::githubToken());
    }

    public function test_coming_soon_mode_shows_visitors_a_holding_page(): void
    {
        Post::factory()->create(['title' => 'Secret feed post', 'posted_at' => now()->subMinute()]);

        $this->saveSettings(['maintenance_enabled' => true, 'maintenance_message' => 'Back on Monday.'], 'maintenance')
            ->assertHasNoErrors();

        $this->home()
            ->assertStatus(503)
            ->assertSee('Back on Monday.')
            ->assertDontSee('Secret feed post');

        $this->actingAs($this->admin())->get('/')
            ->assertOk()
            ->assertSee('Secret feed post')
            ->assertSee('Coming soon mode is on');
    }

    public function test_cache_can_be_cleared(): void
    {
        cache()->put('stale', 'value');

        Livewire::actingAs($this->admin())->test(SiteSettings::class, ['section' => 'maintenance'])
            ->call('clearCache')
            ->assertDispatched('notify', message: 'Cache cleared.');

        $this->assertNull(cache()->get('stale'));
    }

    public function test_settings_load_with_one_query(): void
    {
        Setting::set('site_title', 'Cached title');
        app()->forgetInstance('settings.values');

        \DB::enableQueryLog();
        Setting::get('site_title');
        Setting::get('accent_color');
        Setting::get('footer_text');

        $this->assertCount(1, \DB::getQueryLog());
        $this->assertSame('Cached title', Setting::get('site_title'));
    }
}
