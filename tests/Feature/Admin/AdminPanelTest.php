<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\SiteSettings;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_pages_use_the_sidebar_layout(): void
    {
        $this->actingAs($this->admin());

        foreach (['dashboard', 'posts.index', 'posts.create', 'settings', 'profile.edit', 'user-password.edit'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('id="adminSidebar"', false)
                ->assertSee('Site settings');
        }
    }

    public function test_sidebar_shows_draft_and_scheduled_counts(): void
    {
        Post::factory()->count(2)->create(['is_draft' => true]);
        Post::factory()->create(['posted_at' => now()->addDay()]);
        Post::factory()->create(['posted_at' => now()->subDay()]);

        $this->actingAs($this->admin())
            ->get(route('posts.index'))
            ->assertSeeInOrder(['Drafts', '2', 'Scheduled', '1'], false);
    }

    public function test_non_admin_account_pages_hide_admin_navigation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Site settings');
    }

    public function test_posts_can_be_searched(): void
    {
        Post::factory()->create(['title' => 'Laravel tips']);
        Post::factory()->create(['title' => 'Holiday photos']);

        Livewire::actingAs($this->admin())
            ->test(PostIndex::class)
            ->set('search', 'laravel')
            ->assertSee('Laravel tips')
            ->assertDontSee('Holiday photos');
    }

    public function test_posts_can_be_filtered_by_type_and_status(): void
    {
        Post::factory()->create(['title' => 'Published blog', 'posted_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'Draft blog', 'is_draft' => true]);
        Post::factory()->create(['title' => 'Future blog', 'posted_at' => now()->addDay()]);
        Post::factory()->create(['title' => 'A quote', 'type' => 'quote', 'posted_at' => now()->subDay()]);
        Post::factory()->create(['title' => 'Pinned blog', 'is_pinned' => true, 'posted_at' => now()->subDay()]);

        $component = Livewire::actingAs($this->admin())->test(PostIndex::class);

        $component->set('type', 'quote')
            ->assertSee('A quote')->assertDontSee('Published blog');

        $component->set('type', '')->set('status', 'draft')
            ->assertSee('Draft blog')->assertDontSee('Published blog');

        $component->set('status', 'scheduled')
            ->assertSee('Future blog')->assertDontSee('Draft blog')->assertDontSee('Published blog');

        $component->set('status', 'published')
            ->assertSee('Published blog')->assertDontSee('Future blog')->assertDontSee('Draft blog');

        $component->set('status', 'pinned')
            ->assertSee('Pinned blog')->assertDontSee('Published blog');

        $component->call('clearFilters')
            ->assertSet('status', '')
            ->assertSee('Draft blog')->assertSee('Future blog');
    }

    public function test_pin_and_publish_can_be_toggled(): void
    {
        $post = Post::factory()->create(['is_draft' => true, 'posted_at' => null]);

        Livewire::actingAs($this->admin())
            ->test(PostIndex::class)
            ->call('togglePin', $post->id)
            ->assertDispatched('notify')
            ->call('togglePublished', $post->id);

        $post->refresh();
        $this->assertTrue($post->is_pinned);
        $this->assertFalse($post->is_draft);
        $this->assertNotNull($post->posted_at);
    }

    public function test_bulk_actions_apply_to_selected_posts(): void
    {
        [$first, $second, $untouched] = Post::factory()->count(3)->create(['is_draft' => true]);
        $selected = [(string) $first->id, (string) $second->id];

        $component = Livewire::actingAs($this->admin())->test(PostIndex::class);

        $component->set('selected', $selected)->call('bulk', 'publish')->assertSet('selected', []);
        $this->assertFalse($first->fresh()->is_draft);
        $this->assertFalse($second->fresh()->is_draft);
        $this->assertTrue($untouched->fresh()->is_draft);

        $component->set('selected', $selected)->call('bulk', 'draft');
        $this->assertTrue($first->fresh()->is_draft);

        $component->set('selected', $selected)->call('bulk', 'delete');
        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($untouched);
    }

    public function test_settings_save_shows_a_toast(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SiteSettings::class)
            ->set('site_title', 'New title')
            ->call('save')
            ->assertDispatched('notify', message: 'Settings saved.');
    }

    public function test_logged_out_auth_pages_render_without_flux(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Email password reset link')
            ->assertDontSee('<flux:', false)
            ->assertSee('auth-body', false);

        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'a@b.test']))
            ->assertOk()
            ->assertSee('value="a@b.test"', false);
    }
}
