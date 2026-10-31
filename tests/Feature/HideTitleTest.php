<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HideTitleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_title_can_be_hidden_from_the_editor(): void
    {
        Livewire::actingAs($this->admin())->test(PostForm::class)
            ->set('type', 'blog')
            ->set('title', 'Secret heading')
            ->set('content', 'Visible content')
            ->set('hide_title', true)
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $this->assertTrue($post->hide_title);

        Livewire::actingAs($this->admin())->test(PostForm::class, ['post' => $post])
            ->assertSet('hide_title', true);
    }

    public function test_hidden_title_is_not_shown_on_the_feed_or_in_the_dialog(): void
    {
        $post = Post::factory()->create([
            'title' => 'Secret heading',
            'content' => 'Visible content',
            'hide_title' => true,
            'posted_at' => now()->subMinute(),
        ]);
        Post::factory()->create(['title' => 'Normal heading', 'posted_at' => now()->subMinute()]);

        $this->get('/')
            ->assertSee('Visible content')
            ->assertSee('Normal heading')
            ->assertDontSee('Secret heading');

        Livewire::test('nav-modal')
            ->dispatch('open-modal', id: $post->id)
            ->assertSet('isOpen', true)
            ->assertSet('title', '');
    }

    public function test_hidden_title_applies_to_cards_with_their_own_layout(): void
    {
        foreach (['quote' => 'Hidden author', 'profile' => 'Hidden name', 'image' => 'Hidden caption'] as $type => $title) {
            Post::factory()->create([
                'type' => $type,
                'title' => $title,
                'content' => "Content for {$type}",
                'meta_data' => $type === 'profile' ? ['sns' => []] : [],
                'hide_title' => true,
                'posted_at' => now()->subMinute(),
            ]);
        }

        $this->get('/')
            ->assertSee('Content for quote')
            ->assertDontSee('Hidden author')
            ->assertDontSee('Hidden name')
            ->assertDontSee('Hidden caption');
    }

    public function test_date_can_be_hidden_from_the_editor(): void
    {
        Livewire::actingAs($this->admin())->test(PostForm::class)
            ->set('type', 'blog')
            ->set('title', 'Dated post')
            ->set('content', 'Visible content')
            ->set('hide_date', true)
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $this->assertTrue($post->hide_date);

        Livewire::actingAs($this->admin())->test(PostForm::class, ['post' => $post])
            ->assertSet('hide_date', true);
    }

    public function test_hidden_date_overrides_the_show_dates_setting_for_that_card(): void
    {
        config(['site.show_dates' => true]);

        Post::factory()->create(['title' => 'Shown date', 'posted_at' => now()->setDate(2026, 3, 5)]);
        Post::factory()->create(['title' => 'Hidden date', 'hide_date' => true, 'posted_at' => now()->setDate(2026, 4, 9)]);

        $this->get('/')
            ->assertSee('Hidden date')
            ->assertSee('Mar 5, 2026')
            ->assertDontSee('Apr 9, 2026');
    }

    public function test_posts_list_still_shows_the_title_with_a_badge(): void
    {
        Post::factory()->create(['title' => 'Secret heading', 'hide_title' => true]);

        Livewire::actingAs($this->admin())->test(PostIndex::class)
            ->assertSee('Secret heading')
            ->assertSee('Title hidden');
    }
}
