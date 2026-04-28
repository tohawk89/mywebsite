<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class PostIndex extends Component
{
    use WithPagination;

    public function delete($id): void
    {
        $post = Post::findOrFail($id);
        $post->delete();
    }

    public function moveUp(int $id): void
    {
        $pinnedPosts = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $index = $pinnedPosts->search(fn ($p) => $p->id === $id);

        if ($index === false || $index === 0) {
            return;
        }

        $pinnedPosts->each(function (Post $post, int $i) use ($index): void {
            if ($i === $index - 1) {
                $post->sort_order = $index;
            } elseif ($i === $index) {
                $post->sort_order = $index - 1;
            } else {
                $post->sort_order = $i;
            }
            $post->save();
        });
    }

    public function moveDown(int $id): void
    {
        $pinnedPosts = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $index = $pinnedPosts->search(fn ($p) => $p->id === $id);
        $lastIndex = $pinnedPosts->count() - 1;

        if ($index === false || $index === $lastIndex) {
            return;
        }

        $pinnedPosts->each(function (Post $post, int $i) use ($index): void {
            if ($i === $index) {
                $post->sort_order = $index + 1;
            } elseif ($i === $index + 1) {
                $post->sort_order = $index;
            } else {
                $post->sort_order = $i;
            }
            $post->save();
        });
    }

    public function render(): \Illuminate\View\View
    {
        $pinnedIds = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->pluck('id');

        return view('livewire.admin.post-index', [
            'posts' => Post::pinnedFirst()->paginate(20),
            'firstPinnedId' => $pinnedIds->first(),
            'lastPinnedId' => $pinnedIds->last(),
        ]);
    }
}
