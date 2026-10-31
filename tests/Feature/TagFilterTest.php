<?php

namespace Tests\Feature;

use App\Livewire\Admin\SiteSettings;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\PostTypes\PostTypeRegistry;
use App\Support\SiteOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagFilterTest extends TestCase
{
    use RefreshDatabase;

    private function postTagged(string $title, string $tagName, array $attributes = []): Post
    {
        $post = Post::factory()->create(['title' => $title, 'posted_at' => now()->subMinute(), ...$attributes]);
        $post->tags()->attach(Tag::firstOrCreate(['slug' => Tag::slugFor($tagName)], ['name' => $tagName]));

        return $post;
    }

    public function test_tag_in_the_url_filters_the_feed(): void
    {
        $this->postTagged('Anime post', 'Anime');
        $this->postTagged('Music post', 'Music');

        $this->get('/?tag=anime')
            ->assertOk()
            ->assertSee('Anime post')
            ->assertDontSee('Music post');
    }

    public function test_clicking_a_tag_filters_and_clicking_it_again_clears(): void
    {
        $this->postTagged('Anime post', 'Anime');
        $this->postTagged('Music post', 'Music');

        $feed = Livewire::test('pages::post-feed')
            ->call('loadMore')
            ->dispatch('filter-tag', tag: 'music')
            ->assertSet('tag', 'music')
            ->assertSet('perPage', 10);

        $this->assertSame(['Music post'], $feed->instance()->posts->pluck('title')->all());

        $feed->call('filterByTag', 'music')->assertSet('tag', '');

        $this->assertCount(2, $feed->instance()->posts);
    }

    public function test_tag_bar_lists_only_tags_with_posts_on_the_feed_most_used_first(): void
    {
        $this->postTagged('One', 'Music');
        $this->postTagged('Two', 'Gaming');
        $this->postTagged('Three', 'Gaming');
        $this->postTagged('Draft', 'Secret draft', ['is_draft' => true]);
        $this->postTagged('Page', 'Page only', ['type' => 'page']);
        Tag::create(['name' => 'Unused', 'slug' => 'unused']);

        Livewire::test('pages::post-feed')
            ->assertSeeInOrder(['All', 'Gaming', 'Music'])
            ->assertDontSee('Secret draft')
            ->assertDontSee('Page only')
            ->assertDontSee('Unused');
    }

    public function test_tag_bar_can_be_turned_off_but_a_filter_still_shows_a_way_back(): void
    {
        $this->postTagged('Anime post', 'Anime');

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(SiteSettings::class, ['section' => 'feed'])
            ->set('show_tag_bar', false)
            ->call('save')
            ->assertHasNoErrors();

        config(SiteOptions::toConfig());

        $this->get('/')->assertDontSee('feed-tag-bar', false);

        $this->get('/?tag=anime')
            ->assertDontSee('feed-tag-bar', false)
            ->assertSee('feed-filter-note', false)
            ->assertSee('Anime post');
    }

    public function test_unknown_tag_shows_an_empty_message(): void
    {
        $this->postTagged('Anime post', 'Anime');

        $this->get('/?tag=nope')
            ->assertOk()
            ->assertDontSee('Anime post')
            ->assertSee('No posts tagged nope yet.');
    }

    public function test_every_feed_card_shows_its_tags(): void
    {
        $types = array_diff(PostTypeRegistry::all(), ['page']);

        foreach ($types as $type) {
            $this->postTagged("A {$type}", "tag-{$type}", [
                'type' => $type,
                'meta_data' => ['repo_url' => 'https://github.com/a/b', 'url' => 'https://example.com', 'sns' => []],
            ]);
        }

        $response = $this->get('/')->assertOk();

        foreach ($types as $type) {
            $response->assertSee('href="'.route('home', ['tag' => "tag-{$type}"]).'"', false);
        }
    }

    public function test_card_tags_link_to_the_filtered_feed(): void
    {
        $this->postTagged('Anime post', 'Anime');

        $this->get('/')->assertSee('href="'.route('home', ['tag' => 'anime']).'"', false);
    }
}
