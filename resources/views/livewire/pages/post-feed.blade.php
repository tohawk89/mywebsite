<?php

use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component {
    use WithPagination;

    public int $perPage = 10;

    public function mount(): void
    {
        $this->perPage = config('site.posts_per_page', 10);
    }

    public function loadMore(): void
    {
        $this->perPage += config('site.posts_per_page', 10);
    }

    #[Computed]
    public function posts()
    {
        return Post::where('type', '!=', 'page')
            ->published()
            ->whereNotIn('type', config('site.hidden_types', []))
            ->with(['tags', 'media'])
            ->pinnedFirst()
            ->paginate($this->perPage);
    }
};
?>

<div class="container text-center py-4">
    <div @class(['feed-grid', 'is-max-3' => config('site.max_columns') === 3]) id="feed-grid" wire:ignore.self>
        @foreach($this->posts as $post)
            <livewire:dynamic-component
                :is="'posts.' . $post->type . '.card'"
                :post="$post"
                :key="'post-' . $post->id"
            />
        @endforeach
    </div>

    {{-- Loads more automatically on scroll, or with a button (Site settings > Feed). --}}
    @php($loadWithButton = config('site.feed_loading') === 'button')
    <div class="text-center my-5" @unless($loadWithButton) x-data x-intersect="$wire.loadMore()" @endunless>

        @if($this->posts->hasMorePages())
            @if($loadWithButton)
                <button type="button" class="btn btn-outline-dark rounded-0 px-4 feed-load-more" wire:click="loadMore"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="loadMore">Load more</span>
                    <span wire:loading wire:target="loadMore">Loading…</span>
                </button>
            @else
                <div wire:loading>
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-light border-0 shadow-sm rounded-pill d-inline-block px-4 py-2 text-muted fw-medium">
                You've reached the end of the line! 🎉
            </div>
        @endif
    </div>

</div>
