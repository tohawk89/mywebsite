{{--
    Default card body: tags, title and a three-line content excerpt, plus any extra slot content.
    Renders nothing when all of those are empty (or only a bottom spacer after inset media).
    With "expandable", the full content is shown with a "Read more" toggle when it runs past three lines.
--}}
@props([
    'post',
    'title' => true,
    'excerpt' => true,
    'expandable' => false,
    'spacer' => false,
])

@php
    $showTitle = $title && filled($post->title);
    $showExcerpt = $excerpt && filled($post->content);
@endphp

@if($post->tags->isNotEmpty() || $showTitle || $showExcerpt || $slot->isNotEmpty())
    <div class="card-body">
        <x-ui.post-tags :tags="$post->tags" />

        @if($showTitle)
            <h5 class="card-title fw-bold font-sans @unless($showExcerpt || $slot->isNotEmpty()) mb-0 @endunless">{{ $post->title }}</h5>
        @endif

        @if($showExcerpt && $expandable)
            <div x-data="{ expanded: false, overflowing: false, measure() { this.overflowing = this.expanded || this.$refs.text.scrollHeight > this.$refs.text.clientHeight + 1 } }"
                x-init="$nextTick(() => measure())" @resize.window.debounce.200ms="measure()">
                <p x-ref="text" class="card-text text-muted font-serif line-clamp-3 text-prewrap mb-0"
                    :class="{ 'line-clamp-3': ! expanded }">{{ $post->content }}</p>
                <button type="button" class="btn btn-link btn-sm p-0 mt-1 text-decoration-none card-read-more" x-show="overflowing" x-cloak
                    @click.stop="expanded = ! expanded" :aria-expanded="expanded"
                    x-text="expanded ? 'Show less' : 'Read more'">Read more</button>
            </div>
        @elseif($showExcerpt)
            <p class="card-text text-muted font-serif line-clamp-3 @if($slot->isEmpty()) mb-0 @endif">{{ \Illuminate\Support\Str::limit($post->content, 300) }}</p>
        @endif

        {{ $slot }}
    </div>
@elseif($spacer)
    <div class="pb-3"></div>
@endif
