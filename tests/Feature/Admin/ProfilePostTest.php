<?php

namespace Tests\Feature\Admin;

use App\Enums\SnsType;
use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePostTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_can_create_profile_post_with_title_and_description(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('title', 'John Doe')
            ->set('content', 'A short bio about me.')
            ->call('save')
            ->assertRedirect(route('posts.index'));

        $post = Post::where('type', 'profile')->first();
        $this->assertNotNull($post);
        $this->assertSame('John Doe', $post->title);
        $this->assertSame('A short bio about me.', $post->content);
    }

    public function test_can_create_profile_post_with_sns_links(): void
    {
        $this->actingAsUser();

        $sns = [
            ['platform' => SnsType::GitHub->value, 'url' => 'https://github.com/johndoe'],
            ['platform' => SnsType::Twitter->value, 'url' => 'https://twitter.com/johndoe'],
        ];

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('title', 'John Doe')
            ->set('typeData', ['sns' => $sns])
            ->call('save')
            ->assertRedirect(route('posts.index'));

        $post = Post::where('type', 'profile')->first();
        $this->assertCount(2, $post->meta_data['sns']);
        $this->assertSame(SnsType::GitHub->value, $post->meta_data['sns'][0]['platform']);
        $this->assertSame('https://github.com/johndoe', $post->meta_data['sns'][0]['url']);
    }

    public function test_sns_url_must_be_valid(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('title', 'John Doe')
            ->set('typeData', ['sns' => [['platform' => SnsType::GitHub->value, 'url' => 'not-a-url']]])
            ->call('save')
            ->assertHasErrors(['typeData.sns.0.url']);
    }

    public function test_sns_platform_must_be_valid(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('title', 'John Doe')
            ->set('typeData', ['sns' => [['platform' => 'invalid_platform', 'url' => 'https://example.com']]])
            ->call('save')
            ->assertHasErrors(['typeData.sns.0.platform']);
    }

    public function test_add_sns_appends_new_entry_with_default_platform(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->call('addSns')
            ->assertSet('typeData.sns.0.platform', SnsType::GitHub->value)
            ->assertSet('typeData.sns.0.url', '');
    }

    public function test_remove_sns_deletes_entry_by_index(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('typeData', ['sns' => [
                ['platform' => SnsType::GitHub->value, 'url' => 'https://github.com/a'],
                ['platform' => SnsType::Twitter->value, 'url' => 'https://twitter.com/b'],
            ]])
            ->call('removeSns', 0)
            ->assertCount('typeData.sns', 1)
            ->assertSet('typeData.sns.0.platform', SnsType::Twitter->value);
    }

    public function test_profile_post_title_is_required(): void
    {
        $this->actingAsUser();

        Livewire::test(PostForm::class)
            ->set('type', 'profile')
            ->set('title', '')
            ->call('save')
            ->assertHasErrors(['title']);
    }

    public function test_can_edit_existing_profile_post(): void
    {
        $this->actingAsUser();

        $post = Post::factory()->create([
            'type' => 'profile',
            'title' => 'Old Name',
            'content' => 'Old bio',
            'meta_data' => ['sns' => [['platform' => SnsType::GitHub->value, 'url' => 'https://github.com/old']]],
        ]);

        Livewire::test(PostForm::class, ['post' => $post])
            ->assertSet('title', 'Old Name')
            ->assertSet('typeData.sns.0.platform', SnsType::GitHub->value)
            ->set('title', 'New Name')
            ->set('typeData.sns.0.url', 'https://github.com/new')
            ->call('save');

        $post->refresh();
        $this->assertSame('New Name', $post->title);
        $this->assertSame('https://github.com/new', $post->meta_data['sns'][0]['url']);
    }

    public function test_profile_card_renders_in_feed(): void
    {
        $post = Post::factory()->create([
            'type' => 'profile',
            'title' => 'Jane Doe',
            'content' => 'Developer',
            'posted_at' => now(),
            'meta_data' => ['sns' => [['platform' => SnsType::LinkedIn->value, 'url' => 'https://linkedin.com/in/jane']]],
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Jane Doe')
            ->assertSee('Developer');
    }
}
