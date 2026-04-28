<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class PostFeed extends Component
{
    use WithPagination;

    public $perPage = 10;

    public function mount(): void
    {
        $this->perPage = config('site.posts_per_page', 10);
    }

    // We maintain a list of loaded post IDs to avoid duplicates if we were appending manually,
    // but Livewire pagination handles this well if we just render the paginated result.
    // However, for masonry + infinite scroll, we commonly append.
    // For simplicity with Livewire defacto infinite scrolling, we will use the `paginate` in view.

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function render()
    {
        // Fetch pinned posts separately or include them?
        // User wants: "I can pin and sort which post will load first".
        // Scope `pinnedFirst` does this: orders by pinned=1, then sort_order, then created_at.

        // We exclude 'PAGE' type (About/Contact) from the feed.
        $posts = Post::where('type', '!=', \App\Enums\PostType::PAGE)
            ->whereNotNull('posted_at')
            ->pinnedFirst()
            ->paginate($this->perPage);

        return view('livewire.post-feed', [
            'posts' => $posts,
        ]);
    }
}
