<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use App\PostTypes\Repository\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
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
        $this->assertSame('Git Repository', (new Handler)->label());
    }

    public function test_type_selector_uses_handler_labels(): void
    {
        $typesWithLabels = Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->instance()
            ->typesWithLabels();

        $this->assertContains(['value' => 'repository', 'label' => 'Git Repository', 'icon' => 'bi-git'], $typesWithLabels);
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

    private function createRepositoryPost(string $url): Post
    {
        Livewire::actingAs($this->adminUser())
            ->test(PostForm::class)
            ->set('type', 'repository')
            ->set('typeData', ['repo_url' => $url])
            ->call('save')
            ->assertHasNoErrors();

        return Post::first();
    }

    public function test_gitlab_repository_data_is_fetched_including_nested_groups(): void
    {
        Http::fake([
            'gitlab.com/api/v4/projects/gitlab-org%2Fcharts%2Fgitlab/languages' => Http::response(['Shell' => 12.5, 'Go Template' => 80.1]),
            'gitlab.com/api/v4/projects/gitlab-org%2Fcharts%2Fgitlab' => Http::response([
                'path_with_namespace' => 'gitlab-org/charts/gitlab',
                'description' => 'Cloud native GitLab Helm chart',
                'star_count' => 454,
                'forks_count' => 300,
                'topics' => ['helm', 'kubernetes'],
                'avatar_url' => null,
                'namespace' => ['avatar_url' => '/uploads/-/system/group/avatar/1/charts.png'],
            ]),
        ]);

        $post = $this->createRepositoryPost('https://gitlab.com/gitlab-org/charts/gitlab/-/tree/master/doc');

        $this->assertSame('gitlab-org/charts/gitlab', $post->title);
        $this->assertSame('gitlab-org/charts/gitlab', $post->meta_data['repo_name']);
        $this->assertSame('Cloud native GitLab Helm chart', $post->meta_data['repo_description']);
        $this->assertSame('Go Template', $post->meta_data['language']);
        $this->assertSame(454, $post->meta_data['stars']);
        $this->assertSame(300, $post->meta_data['forks']);
        $this->assertSame(['helm', 'kubernetes'], $post->meta_data['topics']);
        $this->assertSame('https://gitlab.com/uploads/-/system/group/avatar/1/charts.png', $post->meta_data['owner_avatar']);
    }

    public function test_bitbucket_repository_data_is_fetched(): void
    {
        Http::fake([
            'api.bitbucket.org/2.0/repositories/snakeyaml/snakeyaml/watchers*' => Http::response(['size' => 12]),
            'api.bitbucket.org/2.0/repositories/snakeyaml/snakeyaml/forks*' => Http::response(['size' => 3]),
            'api.bitbucket.org/2.0/repositories/snakeyaml/snakeyaml' => Http::response([
                'full_name' => 'snakeyaml/snakeyaml',
                'description' => 'a complete YAML 1.1 processor for JVM',
                'language' => 'java',
                'owner' => ['links' => ['avatar' => ['href' => 'https://bitbucket.org/account/snakeyaml/avatar/']]],
            ]),
        ]);

        $post = $this->createRepositoryPost('https://bitbucket.org/snakeyaml/snakeyaml/src/master/');

        $this->assertSame('snakeyaml/snakeyaml', $post->meta_data['repo_name']);
        $this->assertSame('a complete YAML 1.1 processor for JVM', $post->meta_data['repo_description']);
        $this->assertSame('Java', $post->meta_data['language']);
        $this->assertSame(12, $post->meta_data['stars']);
        $this->assertSame(3, $post->meta_data['forks']);
        $this->assertSame('https://bitbucket.org/account/snakeyaml/avatar/', $post->meta_data['owner_avatar']);
    }

    public function test_dot_git_suffix_is_ignored(): void
    {
        Http::fake([
            'api.github.com/repos/laravel/framework' => Http::response($this->githubApiResponse(), 200),
        ]);

        $post = $this->createRepositoryPost('https://github.com/laravel/framework.git');

        $this->assertSame('laravel/framework', $post->meta_data['repo_name']);
        $this->assertSame('PHP', $post->meta_data['language']);
    }

    public function test_unreachable_host_still_saves_the_post(): void
    {
        Http::fake(fn () => throw new ConnectionException('timed out'));

        $post = $this->createRepositoryPost('https://gitlab.com/gitlab-org/gitlab-runner');

        $this->assertSame('gitlab-org/gitlab-runner', $post->meta_data['repo_name']);
        $this->assertArrayNotHasKey('stars', $post->meta_data);
    }
}
