{{--
    Shell for every feed card. Handles the feed-item wrapper, card styling and the default body.

    <x-post-card :post="$post" color="#FFE4D6" inset-media>
        <x-slot:media><iframe ...></iframe></x-slot:media>
    </x-post-card>

    - media slot: shown above the body (flush; add inset-media to pad embeds like players)
    - default slot: extra content appended to the default body (e.g. a button)
    - body slot: replaces the default body entirely
    - :title="false" / :excerpt="false": hide the title or content in the default body
    - expandable: show the full content with a "Read more" toggle instead of a fixed excerpt
    - other attributes (class, @click...) go on the card element
--}}
@props([
    'post',
    'color' => null,
    'insetMedia' => false,
    'title' => true,
    'excerpt' => true,
    'expandable' => false,
])

<div class="feed-item">
    <div {{ $attributes->class(['card border-0 shadow-sm rounded-0 overflow-hidden text-start'])->merge($color ? ['style' => "background-color: {$color};"] : []) }}>
        @isset($media)
            @if($insetMedia)
                <div class="p-3 pb-0">{{ $media }}</div>
            @else
                {{ $media }}
            @endif
        @endisset

        @isset($body)
            {{ $body }}
        @else
            <x-post-card-body :$post :$title :$excerpt :$expandable :spacer="$insetMedia">{{ $slot }}</x-post-card-body>
        @endisset

        @if(config('site.show_dates') && $post->posted_at && ! $post->hide_date)
            <time class="card-date d-block" datetime="{{ $post->posted_at->toDateString() }}">{{ $post->posted_at->format('M j, Y') }}</time>
        @endif
    </div>
</div>
