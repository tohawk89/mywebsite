<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PostIndex;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostIndexReorderTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function createPinnedPost(int $sortOrder): Post
    {
        return Post::factory()->create([
            'is_pinned' => true,
            'sort_order' => $sortOrder,
            'posted_at' => now(),
        ]);
    }

    public function test_move_up_decreases_sort_order(): void
    {
        $this->actingAsUser();

        $first = $this->createPinnedPost(0);
        $second = $this->createPinnedPost(1);

        Livewire::test(PostIndex::class)
            ->call('moveUp', $second->id);

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_move_down_increases_sort_order(): void
    {
        $this->actingAsUser();

        $first = $this->createPinnedPost(0);
        $second = $this->createPinnedPost(1);

        Livewire::test(PostIndex::class)
            ->call('moveDown', $first->id);

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(0, $second->fresh()->sort_order);
    }

    public function test_move_up_does_nothing_for_first_pinned(): void
    {
        $this->actingAsUser();

        $first = $this->createPinnedPost(0);
        $second = $this->createPinnedPost(1);

        Livewire::test(PostIndex::class)
            ->call('moveUp', $first->id);

        $this->assertSame(0, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);
    }

    public function test_move_down_does_nothing_for_last_pinned(): void
    {
        $this->actingAsUser();

        $first = $this->createPinnedPost(0);
        $second = $this->createPinnedPost(1);

        Livewire::test(PostIndex::class)
            ->call('moveDown', $second->id);

        $this->assertSame(0, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);
    }

    public function test_move_handles_duplicate_sort_orders(): void
    {
        $this->actingAsUser();

        // All start at sort_order=0 (default for existing posts)
        $a = $this->createPinnedPost(0);
        $b = $this->createPinnedPost(0);
        $c = $this->createPinnedPost(0);

        // Should not throw — normalizes sort orders
        Livewire::test(PostIndex::class)
            ->call('moveDown', $a->id);

        // After normalization, all should have distinct sort_orders
        $orders = Post::where('is_pinned', true)->pluck('sort_order')->sort()->values();
        $this->assertSame([0, 1, 2], $orders->toArray());
    }

    public function test_newly_pinned_post_gets_last_sort_order(): void
    {
        $this->actingAsUser();

        $this->createPinnedPost(0);
        $this->createPinnedPost(1);

        $newPost = Post::factory()->create(['is_pinned' => false, 'sort_order' => 0]);

        Livewire::test(\App\Livewire\Admin\PostForm::class, ['post' => $newPost])
            ->set('is_pinned', true)
            ->call('save');

        $this->assertSame(2, $newPost->fresh()->sort_order);
    }
}
