{{--
    Default card body: tags, title and a three-line content excerpt, plus any extra slot content.
    Renders nothing when all of those are empty (or only a bottom spacer after inset media).
--}}
@props([
    'post',
    'title' => true,
    'excerpt' => true,
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

        @if($showExcerpt)
            <p class="card-text text-muted font-serif line-clamp-3 @if($slot->isEmpty()) mb-0 @endif">{{ \Illuminate\Support\Str::limit($post->content, 300) }}</p>
        @endif

        {{ $slot }}
    </div>
@elseif($spacer)
    <div class="pb-3"></div>
@endif
