<?php

namespace Tests\Feature\Admin;

use App\Enums\PostType;
use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstagramPostTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_can_create_instagram_post_with_valid_url(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', PostType::INSTAGRAM->value)
            ->set('title', 'My Instagram Post')
            ->set('instagram_url', 'https://www.instagram.com/p/Cx12345abcD/')
            ->call('save')
            ->assertRedirect(route('posts.index'));

        $post = Post::where('type', PostType::INSTAGRAM)->first();

        $this->assertNotNull($post);
        $this->assertSame('https://www.instagram.com/p/Cx12345abcD/', $post->meta_data['instagram_url']);
        $this->assertSame('https://www.instagram.com/p/Cx12345abcD/embed', $post->meta_data['instagram_embed_url']);
    }

    public function test_instagram_url_must_be_valid_instagram_post_or_reel_url(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', PostType::INSTAGRAM->value)
            ->set('title', 'Invalid Instagram')
            ->set('instagram_url', 'https://example.com/not-instagram')
            ->call('save')
            ->assertHasErrors(['instagram_url']);
    }

    public function test_can_edit_existing_instagram_post(): void
    {
        $this->actingAsUser();

        $post = Post::factory()->create([
            'type' => PostType::INSTAGRAM,
            'title' => 'Old Instagram',
            'meta_data' => [
                'instagram_url' => 'https://www.instagram.com/p/OldCode12345/',
                'instagram_embed_url' => 'https://www.instagram.com/p/OldCode12345/embed',
            ],
        ]);

        Livewire::test(PostForm::class, ['post' => $post])
            ->assertSet('instagram_url', 'https://www.instagram.com/p/OldCode12345/')
            ->set('instagram_url', 'https://www.instagram.com/reel/NewCode67890/')
            ->call('save')
            ->assertRedirect(route('posts.index'));

        $post->refresh();

        $this->assertSame('https://www.instagram.com/reel/NewCode67890/', $post->meta_data['instagram_url']);
        $this->assertSame('https://www.instagram.com/reel/NewCode67890/embed', $post->meta_data['instagram_embed_url']);
    }

    public function test_instagram_post_renders_in_feed(): void
    {
        Post::factory()->create([
            'type' => PostType::INSTAGRAM,
            'title' => 'Instagram in feed',
            'posted_at' => now(),
            'meta_data' => [
                'instagram_url' => 'https://www.instagram.com/p/FeedCode12345/',
                'instagram_embed_url' => 'https://www.instagram.com/p/FeedCode12345/embed',
            ],
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Instagram in feed')
            ->assertSee('https://www.instagram.com/p/FeedCode12345/embed');
    }
}
