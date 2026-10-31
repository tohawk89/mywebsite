<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_posts_are_hidden_until_their_publish_date(): void
    {
        Post::factory()->create(['title' => 'Already Out', 'posted_at' => now()->subMinute()]);
        Post::factory()->create(['title' => 'Coming Soon', 'posted_at' => now()->addDay()]);

        $this->get('/')->assertSee('Already Out')->assertDontSee('Coming Soon');

        $this->travel(2)->days();

        $this->get('/')->assertSee('Coming Soon');
    }

    public function test_feed_is_ordered_by_publish_date(): void
    {
        // Created first but dated later, so it must appear first.
        Post::factory()->create(['title' => 'Newest Post', 'posted_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'Backdated Post', 'posted_at' => now()->subYear()]);

        $this->get('/')->assertSeeInOrder(['Newest Post', 'Backdated Post']);
    }

    public function test_pinned_posts_still_come_first(): void
    {
        Post::factory()->create(['title' => 'Recent Post', 'posted_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'Pinned Post', 'posted_at' => now()->subYear(), 'is_pinned' => true]);

        $this->get('/')->assertSeeInOrder(['Pinned Post', 'Recent Post']);
    }

    public function test_modal_opens_published_posts(): void
    {
        $post = Post::factory()->create(['title' => 'Visible', 'posted_at' => now()->subMinute()]);

        Livewire::test('nav-modal')
            ->dispatch('open-modal', id: $post->id)
            ->assertSet('isOpen', true)
            ->assertSet('title', 'Visible');
    }

    public function test_modal_refuses_drafts_and_scheduled_posts(): void
    {
        $draft = Post::factory()->create(['title' => 'Secret Draft', 'is_draft' => true]);
        $scheduled = Post::factory()->create(['title' => 'Secret Scheduled', 'posted_at' => now()->addDay()]);
        $unpublished = Post::factory()->create(['title' => 'Secret Unpublished', 'posted_at' => null]);

        foreach ([$draft, $scheduled, $unpublished] as $post) {
            Livewire::test('nav-modal')
                ->dispatch('open-modal', id: $post->id)
                ->assertSet('isOpen', false)
                ->assertSet('title', null);
        }
    }

    public function test_tags_differing_only_by_case_share_one_tag(): void
    {
        Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);

        $this->savePostWithTags('laravel, LARAVEL');

        $this->assertSame(['Laravel'], Tag::pluck('name')->all());
        $this->assertCount(1, Post::first()->tags);
    }

    public function test_symbol_tags_stay_distinct(): void
    {
        $this->savePostWithTags('C, C#, C++');

        $this->assertEqualsCanonicalizing(['c', 'c-sharp', 'c-plus-plus'], Tag::pluck('slug')->all());
        $this->assertCount(3, Post::first()->tags);
    }

    public function test_non_latin_tags_can_be_saved(): void
    {
        $this->savePostWithTags('日本, 한국');

        $this->assertEqualsCanonicalizing(['日本', '한국'], Tag::pluck('name')->all());
    }

    private function savePostWithTags(string $tags): void
    {
        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class)
            ->set('type', 'blog')
            ->set('title', 'Tagged')
            ->set('tags', $tags)
            ->call('save')
            ->assertHasNoErrors();
    }
}
