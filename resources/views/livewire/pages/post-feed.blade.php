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
        $this->perPage += 10;
    }

    #[Computed]
    public function posts()
    {
        return Post::where('type', '!=', 'page')
            ->whereNotNull('posted_at')
            ->where('is_draft', false)
            ->pinnedFirst()
            ->paginate($this->perPage);
    }
};
?>

<div class="container text-center py-4">
    <div class="row g-2" id="masonry-grid" wire:ignore.self>
        @foreach($this->posts as $post)
            <livewire:dynamic-component
                :is="'posts.' . $post->type . '.card'"
                :post="$post"
                :key="'post-' . $post->id"
            />
        @endforeach
    </div>

    <div class="text-center my-5" x-data="{ intersect: false }" x-intersect="$wire.loadMore()">

        @if($this->posts->hasMorePages())
            <div wire:loading>
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        @else
            <div class="alert alert-light border-0 shadow-sm rounded-pill d-inline-block px-4 py-2 text-muted fw-medium">
                You've reached the end of the line! 🎉
            </div>
        @endif
    </div>

    @script
    <script>
        Livewire.hook('morph.updated', ({ el, component }) => {
            var grid = document.querySelector('#masonry-grid');
            var msnry = new Masonry(grid, {
                percentPosition: true
            });

            // Re-layout after images load to prevent overlap/gaps
            imagesLoaded(grid).on('progress', function () {
                msnry.layout();
            });
        });
    </script>
    @endscript
</div>
