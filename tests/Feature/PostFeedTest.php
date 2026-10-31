<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        Post::factory()->create(['title' => 'About Page', 'type' => 'page', 'posted_at' => now()]);

        $this->get('/')->assertDontSee('About Page');
    }

    public function test_load_more_increases_per_page(): void
    {
        Livewire::test('pages::post-feed')
            ->assertSet('perPage', 10)
            ->call('loadMore')
            ->assertSet('perPage', 20);
    }

    public function test_load_more_uses_posts_per_page_setting(): void
    {
        config(['site.posts_per_page' => 6]);

        Livewire::test('pages::post-feed')
            ->assertSet('perPage', 6)
            ->call('loadMore')
            ->assertSet('perPage', 12);
    }

    public function test_feed_query_count_does_not_grow_with_posts(): void
    {
        $countQueries = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get('/')->assertOk();

            return count(DB::getQueryLog());
        };

        $this->createTaggedPosts(2);
        $few = $countQueries();

        $this->createTaggedPosts(6);
        $many = $countQueries();

        $this->assertSame($few, $many);
    }

    public function test_selected_site_font_is_loaded(): void
    {
        config(['site.font' => 'Playfair Display']);

        $this->get('/')->assertSee('https://fonts.bunny.net/css?family=playfair-display:400,500,600,700', false);
    }

    private function createTaggedPosts(int $count): void
    {
        $tag = Tag::firstOrCreate(['slug' => 'demo'], ['name' => 'Demo']);

        Post::factory()->count($count)->create(['posted_at' => now()->subMinute()])
            ->each(fn (Post $post) => $post->tags()->attach($tag));
    }
}
