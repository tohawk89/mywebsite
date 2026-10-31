<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="feed-item" style="cursor: pointer;"
    @click="$dispatch('open-modal', { id: {{ $post->id }} })">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden position-relative">
        @if($post->hasMedia('cover'))
            <img src="{{ $post->getFirstMediaUrl('cover') }}"
                class="d-block w-100 h-auto"
                alt="{{ $post->title }}" loading="lazy">
        @else
            <div class="ratio ratio-4x3 bg-secondary text-white">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-image fs-1"></i>
                </div>
            </div>
        @endif

        @if($post->title)
            <div class="feed-item-caption position-absolute bottom-0 start-0 w-100 p-3 text-white">
                <small class="fw-bold">{{ $post->title }}</small>
            </div>
        @endif
    </div>
</div>
