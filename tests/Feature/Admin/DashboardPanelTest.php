<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_stats_count_posts_by_status(): void
    {
        Post::factory()->count(3)->create(['posted_at' => now()->subMinute()]);
        Post::factory()->create(['posted_at' => now()->subMonths(2)]);
        Post::factory()->count(2)->create(['is_draft' => true]);
        Post::factory()->create(['posted_at' => now()->addDay()]);

        $stats = Livewire::actingAs($this->admin())->test(Dashboard::class)->instance()->stats();

        $this->assertSame(4, $stats['published']);
        $this->assertSame(2, $stats['drafts']);
        $this->assertSame(1, $stats['scheduled']);
        $this->assertSame(3, $stats['this_month']);
    }

    public function test_drafts_and_upcoming_posts_are_listed(): void
    {
        Post::factory()->create(['title' => 'Half-written draft', 'is_draft' => true]);
        Post::factory()->create(['title' => 'Tomorrow post', 'posted_at' => now()->addDay()]);
        Post::factory()->create(['title' => 'Old published', 'posted_at' => now()->subDay()]);

        Livewire::actingAs($this->admin())->test(Dashboard::class)
            ->assertSee('Half-written draft')
            ->assertSee('Tomorrow post')
            ->assertDontSee('Old published');
    }

    public function test_posts_per_month_covers_twelve_months(): void
    {
        Post::factory()->count(2)->create(['posted_at' => now()->subMinute()]);
        Post::factory()->create(['posted_at' => now()->startOfMonth()->subMonths(3)->addDay()]);
        Post::factory()->create(['posted_at' => now()->subYears(2)]);

        $months = Livewire::actingAs($this->admin())->test(Dashboard::class)->instance()->postsPerMonth();

        $this->assertCount(12, $months);
        $this->assertSame(2, end($months)['count']);
        $this->assertSame(1, $months[8]['count']);
        $this->assertSame(3, array_sum(array_column($months, 'count')));
    }

    public function test_content_mix_and_top_tags(): void
    {
        Post::factory()->count(2)->create(['type' => 'quote']);
        Post::factory()->create(['type' => 'blog'])->tags()->attach(Tag::create(['name' => 'Laravel', 'slug' => 'laravel']));
        Tag::create(['name' => 'Unused', 'slug' => 'unused']);

        $component = Livewire::actingAs($this->admin())->test(Dashboard::class);

        $this->assertSame(['type' => 'quote', 'label' => 'Quote', 'icon' => 'bi-quote', 'count' => 2], $component->instance()->typeMix()[0]);
        $this->assertSame(['Laravel'], $component->instance()->topTags()->pluck('name')->all());
    }

    public function test_health_checks_flag_posts_that_need_attention(): void
    {
        Post::factory()->create(['type' => 'image', 'title' => 'Empty gallery']);
        Post::factory()->create(['type' => 'link', 'meta_data' => ['url' => 'https://example.com']]);
        Post::factory()->create(['type' => 'link', 'meta_data' => ['url' => 'https://example.com/b']]);

        Livewire::actingAs($this->admin())->test(Dashboard::class)
            ->assertSee('1 image post has no photos.')
            ->assertSee('2 link posts have no page preview.')
            ->assertSee('All repository posts have their details.');
    }

    public function test_quick_create_links_preselect_the_type(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertSee(route('posts.create', ['type' => 'youtube']), false);

        $this->get(route('posts.create', ['type' => 'youtube']))->assertOk();

        Livewire::withQueryParams(['type' => 'youtube'])
            ->actingAs($this->admin())
            ->test(PostForm::class)
            ->assertSet('type', 'youtube');

        Livewire::withQueryParams(['type' => 'not-a-type'])
            ->actingAs($this->admin())
            ->test(PostForm::class)
            ->assertSet('type', 'blog');
    }
}
