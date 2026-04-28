@props(['tags'])

@if($tags->isNotEmpty())
    <div class="mb-2">
        @foreach($tags as $tag)
            @php
                $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary', 'dark'];
                $colorIndex = crc32($tag->name) % count($colors);
                $color = $colors[$colorIndex];
            @endphp
            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-0 mb-1 border border-{{ $color }}-subtle">{{ $tag->name }}</span>
        @endforeach
    </div>
@endif
