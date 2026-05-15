<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_published_posts_appear_in_feed(): void
    {
        Post::factory()->create(['title' => 'Hello World', 'posted_at' => now()]);

        $this->get('/')->assertSee('Hello World');
    }

    public function test_draft_posts_are_excluded_from_feed(): void
    {
        Post::factory()->create(['title' => 'Draft Post', 'is_draft' => true, 'posted_at' => now()]);

        $this->get('/')->assertDontSee('Draft Post');
    }

    public function test_unpublished_posts_are_excluded_from_feed(): void
    {
        Post::factory()->create(['title' => 'Unpublished Post', 'posted_at' => null]);

        $this->get('/')->assertDontSee('Unpublished Post');
    }

    public function test_page_type_posts_are_excluded_from_feed(): void
    {
        Post::factory()->create(['title' => 'About Page', 'type' => PostType::PAGE, 'posted_at' => now()]);

        $this->get('/')->assertDontSee('About Page');
    }

    public function test_load_more_increases_per_page(): void
    {
        Livewire::test('pages::post-feed')
            ->assertSet('perPage', 10)
            ->call('loadMore')
            ->assertSet('perPage', 20);
    }
}
