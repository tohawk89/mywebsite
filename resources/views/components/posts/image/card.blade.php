<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" class="position-relative" style="cursor: pointer;"
    @click="$dispatch('open-modal', { id: {{ $post->id }} })">
    <x-slot:media>
        @if($post->hasMedia('cover'))
            <img src="{{ $post->getFirstMediaUrl('cover') }}" class="d-block w-100 h-auto"
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
        @if($post->title)
            <div class="feed-item-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                <small class="fw-bold">{{ $post->title }}</small>
            </div>
        @endif
    </x-slot:body>
</x-post-card>
