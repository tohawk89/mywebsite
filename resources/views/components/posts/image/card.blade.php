<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

@php
    $images = \App\PostTypes\Image\Handler::images($post);
    $slides = $images->map(fn ($media) => ['href' => $media->getUrl(), 'title' => $post->title])->values();
    // Built here because @js isn't compiled inside component tag attributes.
    $openViewer = 'window.openLightbox('.\Illuminate\Support\Js::from($slides)->toHtml().')';
@endphp

{{-- Clicking opens the photos fullscreen (resources/js/lightbox.js); several photos become a slider. --}}
<x-post-card :post="$post" class="position-relative" style="cursor: zoom-in;"
    role="button" tabindex="0" aria-label="View {{ $images->count() > 1 ? $images->count().' photos' : 'photo' }} fullscreen"
    x-on:click="{{ $openViewer }}"
    x-on:keydown.enter="{{ $openViewer }}">
    <x-slot:media>
        @if($images->isNotEmpty())
            <img src="{{ $images->first()->getUrl() }}" class="d-block w-100 h-auto"
                alt="{{ $post->title }}" loading="lazy">
        @else
            <div class="ratio ratio-4x3 bg-secondary text-white">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-image fs-1"></i>
                </div>
            </div>
        @endif
    </x-slot:media>

    <x-slot:body>
        @if($images->count() > 1)
            <span class="image-post-count"><i class="bi bi-images"></i> {{ $images->count() }}</span>
        @endif
        @if($post->title)
            <div class="feed-item-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                <small class="fw-bold">{{ $post->title }}</small>
            </div>
        @endif
    </x-slot:body>
</x-post-card>
