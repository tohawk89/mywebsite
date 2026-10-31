<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

@php
    $meta = $post->meta_data ?? [];
    $url = $meta['url'] ?? '';
    $domain = $meta['domain'] ?? \App\PostTypes\Link\Handler::domain($url);
@endphp

{{-- Your own title and content (optional) sit above a preview of the shared page. --}}
<x-post-card :post="$post" class="bg-white" expandable>
    @if(! empty($meta['image']))
        <x-slot:media>
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true">
                <img src="{{ $meta['image'] }}" class="card-img-top rounded-0" alt="" loading="lazy" referrerpolicy="no-referrer"
                    onerror="this.closest('a').remove()">
            </a>
        </x-slot:media>
    @endif

    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="link-preview">
        <span class="d-flex align-items-center gap-2 small text-muted mb-1">
            @if(! empty($meta['favicon']))
                <img src="{{ $meta['favicon'] }}" alt="" width="16" height="16" loading="lazy" referrerpolicy="no-referrer"
                    onerror="this.remove()">
            @else
                <i class="bi bi-globe2"></i>
            @endif
            <span class="text-truncate">{{ $meta['site_name'] ?? $domain }}</span>
            <i class="bi bi-box-arrow-up-right ms-auto flex-shrink-0"></i>
        </span>
        <span class="d-block fw-semibold text-dark">{{ $meta['page_title'] ?? $url }}</span>
        @if(! empty($meta['description']))
            <span class="d-block small text-muted link-preview-description">{{ $meta['description'] }}</span>
        @endif
        <span class="d-block small text-muted text-truncate mt-1">{{ $domain }}</span>
    </a>
</x-post-card>
