<?php

namespace Tests\Feature;

use App\Livewire\Admin\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteBackground;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class SiteBackgroundTest extends TestCase
{
    use RefreshDatabase;

    private function settings(): Testable
    {
        return Livewire::actingAs(User::factory()->create(['is_admin' => true]))->test(SiteSettings::class);
    }

    /**
     * Saved options take effect on the next request, when AppServiceProvider loads them.
     *
     * @param  array<string, mixed>  $options
     */
    private function homeWith(array $options): TestResponse
    {
        SiteBackground::save(array_merge(SiteBackground::defaults(), $options));
        config(['site.background' => SiteBackground::load()]);

        return $this->get('/')->assertOk();
    }

    public function test_defaults_to_a_plain_color_background(): void
    {
        $this->assertSame('color', SiteBackground::load()['type']);

        $this->get('/')
            ->assertSee('class="site-bg"', false)
            ->assertDontSee('data-site-background', false);
    }

    public function test_gradient_background_renders_its_stops_and_animation(): void
    {
        $this->homeWith([
            'type' => 'gradient',
            'gradient_from' => '#111111',
            'gradient_via' => '#222222',
            'gradient_to' => '#333333',
            'gradient_angle' => 90,
            'gradient_animate' => true,
        ])
            ->assertSee('linear-gradient(90deg, #111111, #222222, #333333)', false)
            ->assertSee('site-bg-layer is-animated', false);
    }

    public function test_pattern_background_renders_css_pattern(): void
    {
        $this->homeWith(['type' => 'pattern', 'pattern' => 'grid', 'pattern_color' => '#abcdef', 'pattern_size' => 32])
            ->assertSee('linear-gradient(90deg, #abcdef 1px, transparent 1px)', false)
            ->assertSee('background-size: 32px 32px', false);
    }

    public function test_animated_background_renders_canvas_with_effect_config(): void
    {
        $response = $this->homeWith([
            'type' => 'animated',
            'effect' => 'matrix',
            'effect_color' => '#00ff00',
            'effect_speed' => 7,
            'matrix_charset' => 'custom',
            'matrix_text' => 'NAZAR',
            'effect_opacity' => 60,
        ]);

        $response->assertSee('data-site-background', false)->assertSee('opacity: 0.6', false);

        preg_match('/data-site-background="([^"]+)"/', $response->getContent(), $matches);
        $config = json_decode(html_entity_decode($matches[1]), true);

        $this->assertSame('matrix', $config['effect']);
        $this->assertSame('#00ff00', $config['color']);
        $this->assertSame(7, $config['speed']);
        $this->assertSame('NAZAR', $config['text']);
    }

    public function test_settings_save_background_options(): void
    {
        $this->settings()
            ->set('background.type', 'animated')
            ->set('background.effect', 'starfield')
            ->set('background.effect_density', 8)
            ->set('background.disable_on_mobile', true)
            ->call('save')
            ->assertHasNoErrors();

        $saved = SiteBackground::load();
        $this->assertSame('animated', $saved['type']);
        $this->assertSame('starfield', $saved['effect']);
        $this->assertSame(8, $saved['effect_density']);
        $this->assertTrue($saved['disable_on_mobile']);
    }

    public function test_invalid_background_options_are_rejected(): void
    {
        $this->settings()
            ->set('background.type', 'video')
            ->set('background.effect', 'fireworks')
            ->set('background.gradient_from', 'red')
            ->set('background.effect_speed', 50)
            ->set('background.matrix_text', str_repeat('x', 41))
            ->call('save')
            ->assertHasErrors([
                'background.type',
                'background.effect',
                'background.gradient_from',
                'background.effect_speed',
                'background.matrix_text',
            ]);

        $this->assertNull(Setting::get('background'));
    }

    public function test_background_image_is_uploaded_replaced_and_removed(): void
    {
        Storage::fake(SiteBackground::disk());

        $component = $this->settings()
            ->set('background.type', 'image')
            ->set('background_image', UploadedFile::fake()->image('first.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $first = SiteBackground::load()['image_path'];
        Storage::disk(SiteBackground::disk())->assertExists($first);
        $this->homeWith(SiteBackground::load())->assertSee(basename($first));

        $component->set('background_image', UploadedFile::fake()->image('second.jpg'))->call('save');
        $second = SiteBackground::load()['image_path'];
        Storage::disk(SiteBackground::disk())->assertMissing($first);
        Storage::disk(SiteBackground::disk())->assertExists($second);

        $component->call('removeBackgroundImage')->call('save');
        $this->assertSame('', SiteBackground::load()['image_path']);
        Storage::disk(SiteBackground::disk())->assertMissing($second);
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake(SiteBackground::disk());

        $this->settings()
            ->set('background_image', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
            ->assertHasErrors(['background_image']);
    }

    public function test_settings_page_shows_a_live_preview(): void
    {
        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(SiteSettings::class, ['section' => 'appearance'])
            ->set('background.type', 'animated')
            ->assertSee('site-bg is-preview', false)
            ->assertSee('data-site-background', false)
            ->set('background.type', 'pattern')
            ->assertDontSee('data-site-background', false);
    }
}
