<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_settings_page_is_accessible(): void
    {
        $this->actingAsUser();

        $this->get(route('settings'))->assertSeeLivewire(SiteSettings::class);
    }

    public function test_settings_page_requires_auth(): void
    {
        $this->get(route('settings'))->assertRedirect(route('login'));
    }

    public function test_can_save_all_settings(): void
    {
        $this->actingAsUser();

        Livewire::test(SiteSettings::class)
            ->set('site_title', 'Nazar Blog')
            ->set('site_description', 'A developer blog')
            ->set('footer_text', '© 2026 Nazar')
            ->set('background_color', '#ffffff')
            ->set('accent_color', '#000000')
            ->set('font', 'Inter')
            ->set('posts_per_page', 20)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nazar Blog', Setting::get('site_title'));
        $this->assertSame('A developer blog', Setting::get('site_description'));
        $this->assertSame('© 2026 Nazar', Setting::get('footer_text'));
        $this->assertSame('#ffffff', Setting::get('background_color'));
        $this->assertSame('#000000', Setting::get('accent_color'));
        $this->assertSame('Inter', Setting::get('font'));
        $this->assertSame('20', Setting::get('posts_per_page'));
    }

    public function test_site_title_is_required(): void
    {
        $this->actingAsUser();

        Livewire::test(SiteSettings::class)
            ->set('site_title', '')
            ->call('save')
            ->assertHasErrors(['site_title']);
    }

    public function test_background_color_must_be_valid_hex(): void
    {
        $this->actingAsUser();

        Livewire::test(SiteSettings::class)
            ->set('background_color', 'not-a-color')
            ->call('save')
            ->assertHasErrors(['background_color']);
    }

    public function test_font_must_be_in_allowed_list(): void
    {
        $this->actingAsUser();

        Livewire::test(SiteSettings::class)
            ->set('font', 'Comic Sans')
            ->call('save')
            ->assertHasErrors(['font']);
    }

    public function test_posts_per_page_must_be_between_1_and_100(): void
    {
        $this->actingAsUser();

        Livewire::test(SiteSettings::class)
            ->set('posts_per_page', 0)
            ->call('save')
            ->assertHasErrors(['posts_per_page']);

        Livewire::test(SiteSettings::class)
            ->set('posts_per_page', 101)
            ->call('save')
            ->assertHasErrors(['posts_per_page']);
    }

    public function test_loads_existing_settings_on_mount(): void
    {
        $this->actingAsUser();

        Setting::set('site_title', 'Existing Title');
        Setting::set('posts_per_page', '5');

        Livewire::test(SiteSettings::class)
            ->assertSet('site_title', 'Existing Title')
            ->assertSet('posts_per_page', 5);
    }
}
