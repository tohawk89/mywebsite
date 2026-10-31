<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteBackground;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class SiteIconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(SiteBackground::disk());
    }

    private function settings(): Testable
    {
        return Livewire::actingAs(User::factory()->create(['is_admin' => true]))->test(SiteSettings::class);
    }

    public function test_default_icons_are_used_until_one_is_uploaded(): void
    {
        $this->get('/')->assertSee('<link rel="icon" href="/favicon.ico" sizes="any">', false);
        $this->get(route('login'))->assertSee('/favicon.ico', false);
    }

    public function test_uploaded_icon_is_used_on_every_layout(): void
    {
        $this->settings()
            ->set('site_icon_upload', UploadedFile::fake()->image('icon.png', 512, 512))
            ->call('save')
            ->assertHasNoErrors();

        $path = Setting::get('site_icon');
        Storage::disk(SiteBackground::disk())->assertExists($path);

        // Settings load at boot, so mirror that for the next requests.
        config(['site.icon_url' => Storage::disk(SiteBackground::disk())->url($path)]);
        $iconLink = '<link rel="icon" href="'.config('site.icon_url').'">';

        $this->get('/')->assertSee($iconLink, false)->assertDontSee('/favicon.ico', false);

        auth()->logout();
        $this->get(route('login'))->assertOk()->assertSee($iconLink, false);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('dashboard'))->assertSee($iconLink, false);
    }

    public function test_icon_can_be_replaced_and_reset_to_default(): void
    {
        $component = $this->settings()
            ->set('site_icon_upload', UploadedFile::fake()->image('first.png'))
            ->call('save');
        $first = Setting::get('site_icon');

        $component->set('site_icon_upload', UploadedFile::fake()->image('second.png'))->call('save');
        $second = Setting::get('site_icon');
        Storage::disk(SiteBackground::disk())->assertMissing($first);

        $component->call('removeSiteIcon')->call('save');
        $this->assertSame('', Setting::get('site_icon'));
        Storage::disk(SiteBackground::disk())->assertMissing($second);
    }

    public function test_svg_and_large_files_are_rejected(): void
    {
        $this->settings()
            ->set('site_icon_upload', UploadedFile::fake()->create('icon.svg', 2, 'image/svg+xml'))
            ->assertHasErrors(['site_icon_upload']);

        $this->settings()
            ->set('site_icon_upload', UploadedFile::fake()->image('huge.png')->size(2048))
            ->assertHasErrors(['site_icon_upload']);
    }
}
