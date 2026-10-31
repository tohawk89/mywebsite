<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\PostTypes\PostTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Tests\TestCase;

class PostTypeComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_post_type_form_renders(): void
    {
        $component = Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class);

        foreach (PostTypeRegistry::all() as $type) {
            $component->set('type', $type)->assertOk();
        }
    }

    public function test_form_fields_bind_to_their_property(): void
    {
        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class)
            ->set('type', 'instagram')
            ->assertSeeHtml('wire:model="typeData.instagram_url"')
            ->assertSeeHtml('wire:model="title"')
            ->assertSeeHtml('wire:model="content"')
            ->assertSeeHtml('for="field-typeData-instagram_url"')
            ->assertSee('Instagram Post URL');
    }

    public function test_form_input_renders_label_optional_marker_hint_and_error(): void
    {
        // $errors is normally shared by middleware during a request.
        view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['typeData.url' => 'Bad URL'])));

        $html = Blade::render(
            '<x-form.input name="typeData.url" type="url" label="Link" optional hint="Paste a link" placeholder="https://..." size="lg" />',
        );

        $this->assertStringContainsString('type="url"', $html);
        $this->assertStringContainsString('wire:model="typeData.url"', $html);
        $this->assertStringContainsString('placeholder="https://..."', $html);
        $this->assertStringContainsString('form-control-lg', $html);
        $this->assertStringContainsString('(optional)', $html);
        $this->assertStringContainsString('Paste a link', $html);
        $this->assertStringContainsString('Bad URL', $html);
    }

    public function test_form_select_renders_options_and_live_binding(): void
    {
        view()->share('errors', new ViewErrorBag);

        $html = Blade::render(
            '<x-form.select name="typeData.platform" label="Platform" :options="[\'gh\' => \'GitHub\', \'x\' => \'X\']" placeholder="Choose" live />',
        );

        $this->assertStringContainsString('wire:model.live="typeData.platform"', $html);
        $this->assertStringContainsString('<option value="">Choose</option>', $html);
        $this->assertStringContainsString('<option value="gh">GitHub</option>', $html);
    }

    public function test_feed_renders_one_post_of_every_type(): void
    {
        $tag = Tag::create(['name' => 'Demo', 'slug' => 'demo']);
        $metaData = [
            'instagram' => ['instagram_url' => 'https://www.instagram.com/p/Abc123/', 'instagram_embed_url' => 'https://www.instagram.com/p/Abc123/embed'],
            'profile' => ['sns' => [['platform' => 'github', 'url' => 'https://github.com/demo']]],
            'repository' => ['repo_url' => 'https://github.com/laravel/framework', 'repo_name' => 'laravel/framework'],
            'spotify' => ['spotify_url' => 'https://open.spotify.com/embed/track/abc'],
            'youtube' => ['youtube_url' => 'https://youtu.be/dQw4w9WgXcQ'],
        ];

        foreach (array_diff(PostTypeRegistry::all(), ['page']) as $type) {
            Post::factory()->create([
                'type' => $type,
                'title' => "Title for {$type}",
                'content' => "Content for {$type}",
                'meta_data' => $metaData[$type] ?? [],
                'posted_at' => now()->subMinute(),
            ])->tags()->attach($tag);
        }

        $response = $this->get('/')->assertOk();

        foreach (['blog', 'project', 'instagram', 'youtube', 'spotify', 'profile'] as $type) {
            $response->assertSee("Title for {$type}");
        }

        // Default body shows the excerpt; spotify and youtube opt out of it.
        $response->assertSee('Content for blog')->assertSee('Content for instagram')
            ->assertDontSee('Content for spotify')->assertDontSee('Content for youtube');
    }

    public function test_card_body_is_omitted_when_empty(): void
    {
        $post = Post::factory()->create(['title' => '', 'content' => null]);

        $html = Blade::render('<x-post-card :post="$post" color="#fff" inset-media><x-slot:media><span>MEDIA</span></x-slot:media></x-post-card>', ['post' => $post]);

        $this->assertStringContainsString('<div class="p-3 pb-0"><span>MEDIA</span></div>', $html);
        $this->assertStringNotContainsString('card-body', $html);
        $this->assertStringContainsString('<div class="pb-3"></div>', $html);
        $this->assertStringContainsString('background-color: #fff;', $html);
    }
}
