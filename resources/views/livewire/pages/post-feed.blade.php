<?php

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component {
    use WithPagination;

    public int $perPage = 10;

    /** Slug of the tag the feed is filtered by ("" shows everything). */
    #[Url(history: true, except: '')]
    public string $tag = '';

    public function mount(): void
    {
        $this->perPage = config('site.posts_per_page', 10);
    }

    public function loadMore(): void
    {
        $this->perPage += config('site.posts_per_page', 10);
    }

    /** Filters the feed by a tag slug; picking the active tag again clears the filter. */
    #[On('filter-tag')]
    public function filterByTag(string $tag = ''): void
    {
        $this->tag = $this->tag === $tag ? '' : $tag;
        $this->perPage = config('site.posts_per_page', 10);
    }

    /** The tag being filtered by, or null when there's none (or the slug doesn't exist). */
    #[Computed]
    public function activeTag(): ?Tag
    {
        return $this->tag === '' ? null : Tag::where('slug', $this->tag)->first();
    }

    #[Computed]
    public function posts()
    {
        return Post::query()
            ->tap(fn (Builder $query) => $this->onFeed($query))
            ->when($this->tag !== '', fn (Builder $query) => $query->whereHas('tags', fn (Builder $tags) => $tags->where('slug', $this->tag)))
            ->with(['tags', 'media'])
            ->pinnedFirst()
            ->paginate($this->perPage);
    }

    /**
     * Tags used by at least one post on the feed, most used first.
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function feedTags(): Collection
    {
        if (! config('site.show_tag_bar', true)) {
            return collect();
        }

        return Tag::query()
            ->whereHas('posts', fn (Builder $query) => $this->onFeed($query))
            ->withCount(['posts' => fn (Builder $query) => $this->onFeed($query)])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->get();
    }

    /** Limits a post query to what the public feed shows. */
    private function onFeed(Builder $query): Builder
    {
        return $query->published()
            ->where('type', '!=', 'page')
            ->whereNotIn('type', config('site.hidden_types', []));
    }
};
?>

<div class="container text-center py-4">
    @if($this->feedTags->isNotEmpty())
        <nav class="feed-tag-bar" aria-label="Filter posts by tag">
            <button type="button" @class(['feed-tag', 'active' => $tag === '']) wire:click="filterByTag('')"
                @if($tag === '') aria-current="true" @endif>All</button>
            @foreach($this->feedTags as $feedTag)
                <button type="button" @class(['feed-tag', 'active' => $tag === $feedTag->slug])
                    wire:click="filterByTag(@js($feedTag->slug))" wire:key="feed-tag-{{ $feedTag->id }}"
                    @if($tag === $feedTag->slug) aria-current="true" @endif>{{ $feedTag->name }}</button>
            @endforeach
        </nav>
    @elseif($tag !== '')
        {{-- Tag bar is off: still show what the feed is filtered by, with a way back. --}}
        <div class="feed-filter-note">
            Showing <strong>{{ $this->activeTag?->name ?? $tag }}</strong>
            <button type="button" class="btn-close" aria-label="Show all posts" wire:click="filterByTag('')"></button>
        </div>
    @endif

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
        @elseif($this->posts->isEmpty() && $tag !== '')
            <div class="alert alert-light border-0 shadow-sm rounded-pill d-inline-block px-4 py-2 text-muted fw-medium">
                No posts tagged {{ $this->activeTag?->name ?? $tag }} yet.
                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click="filterByTag('')">Show all</button>
            </div>
        @else
            <div class="alert alert-light border-0 shadow-sm rounded-pill d-inline-block px-4 py-2 text-muted fw-medium">
                You've reached the end of the line! 🎉
            </div>
        @endif
    </div>

</div>
