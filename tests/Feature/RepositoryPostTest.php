<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class RepositoryPostTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function githubApiResponse(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'laravel/framework',
            'description' => 'The Laravel Framework.',
            'language' => 'PHP',
            'stargazers_count' => 33000,
            'forks_count' => 11000,
            'topics' => ['laravel', 'php'],
            'owner' => ['avatar_url' => 'https://avatars.githubusercontent.com/u/958072?v=4'],
        ], $overrides);
    }

    public function test_repository_handler_returns_correct_label(): void
    {
        $this->assertSame('Git Repository', (new \App\PostTypes\Repository\Handler)->label());
    }

    public function test_admin_can_create_repository_post_with_github_api_fetch(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/framework' => Http::response($this->githubApiResponse(), 200),
        ]);

        $component = Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', [
                'repo_url' => 'https://github.com/laravel/framework',
                'repo_title' => 'What I used for my website',
                'repo_note' => 'Great framework',
            ])
            ->call('save');

        $component->assertHasNoErrors();

        $post = Post::first();
        $this->assertSame('repository', $post->type);
        $this->assertSame('laravel/framework', $post->title);
        $this->assertSame('https://github.com/laravel/framework', $post->meta_data['repo_url']);
        $this->assertSame('laravel/framework', $post->meta_data['repo_name']);
        $this->assertSame('The Laravel Framework.', $post->meta_data['repo_description']);
        $this->assertSame('PHP', $post->meta_data['language']);
        $this->assertSame(33000, $post->meta_data['stars']);
        $this->assertSame(11000, $post->meta_data['forks']);
        $this->assertSame('What I used for my website', $post->meta_data['title']);
        $this->assertSame('Great framework', $post->meta_data['note']);
    }

    public function test_admin_can_create_repository_post_without_personal_commentary(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/framework' => Http::response($this->githubApiResponse(), 200),
        ]);

        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', ['repo_url' => 'https://github.com/laravel/framework'])
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $this->assertNull($post->meta_data['title']);
        $this->assertNull($post->meta_data['note']);
    }

    public function test_repo_url_is_required_for_repository_type(): void
    {
        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', ['repo_url' => ''])
            ->call('save')
            ->assertHasErrors(['typeData.repo_url' => 'required']);
    }

    public function test_repo_url_must_be_a_valid_git_host(): void
    {
        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', ['repo_url' => 'https://example.com/some/repo'])
            ->call('save')
            ->assertHasErrors(['typeData.repo_url']);
    }

    public function test_repo_title_max_length_is_validated(): void
    {
        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', [
                'repo_url' => 'https://github.com/laravel/framework',
                'repo_title' => str_repeat('a', 256),
            ])
            ->call('save')
            ->assertHasErrors(['typeData.repo_title']);
    }

    public function test_repository_card_renders_on_post_feed(): void
    {
        Post::factory()->repository()->create(['posted_at' => now()]);

        $this->get('/')->assertSee('laravel/framework');
    }

    public function test_api_failure_falls_back_gracefully(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/framework' => Http::response([], 404),
        ]);

        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', ['repo_url' => 'https://github.com/laravel/framework'])
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $this->assertSame('laravel/framework', $post->meta_data['repo_name']);
    }

    public function test_existing_repository_post_fields_are_loaded_in_edit_form(): void
    {
        $post = Post::factory()->repository()->create([
            'meta_data' => [
                'repo_url' => 'https://github.com/laravel/framework',
                'repo_name' => 'laravel/framework',
                'title' => 'My caption',
                'note' => 'My note',
            ],
        ]);

        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class, ['post' => $post])
            ->assertSet('typeData.repo_url', 'https://github.com/laravel/framework')
            ->assertSet('typeData.repo_title', 'My caption')
            ->assertSet('typeData.repo_note', 'My note');
    }
}
