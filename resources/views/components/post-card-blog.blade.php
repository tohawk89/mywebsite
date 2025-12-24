@props(['post'])

@php
    $isPinned = $post->is_pinned;
@endphp

<div class="card rounded-0 border-0 shadow-sm h-100 bg-white {{ $isPinned ? 'border border-primary' : '' }}">
    {{-- Media / Image --}}
    @if($post->hasMedia('cover'))
        <img src="{{ $post->getFirstMediaUrl('cover') }}" class="card-img-top rounded-0" alt="{{ $post->title }}">
    @elseif($post->type === \App\Enums\PostType::IMAGE && isset($post->meta_data['url']))
        <img src="{{ $post->meta_data['url'] }}" class="card-img-top rounded-0" alt="{{ $post->title }}">
    @endif

    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-wrap gap-1">
            @foreach($post->tags as $tag)
                @php
                    $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary', 'dark'];
                    $colorIndex = crc32($tag->name) % count($colors);
                    $color = $colors[$colorIndex];
                @endphp
                <span
                    class="badge bg-{{ $color }}-subtle text-{{ $color }} fw-medium px-3 py-2 rounded-0 border border-{{ $color }}-subtle">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="card-body">
        @if($post->title)
            <h5 class="card-title fw-bold">{{ $post->title }}</h5>
        @endif

        @if($post->content)
            <p class="card-text text-muted">{{ Str::limit($post->content, 150) }}</p>
        @endif

        <button wire:click.prevent="$dispatch('open-modal', { id: {{ $post->id }} })"
            class="btn btn-sm btn-outline-dark rounded-0 mt-2">Read More</button>
    </div>
</div>