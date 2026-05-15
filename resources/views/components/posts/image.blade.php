<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-12 col-lg-8 mb-2"
    style="min-height: 300px; cursor: pointer;"
    @click="$dispatch('open-modal', { id: {{ $post->id }} })">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden h-100 position-relative">
        @if($post->hasMedia('cover'))
            <img src="{{ $post->getFirstMediaUrl('cover') }}"
                class="img-fluid w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                alt="{{ $post->title }}">
        @else
            <div class="w-100 h-100 position-absolute top-0 start-0 bg-secondary d-flex align-items-center justify-content-center text-white">
                <i class="bi bi-image fs-1"></i>
            </div>
        @endif

        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white">
            <small class="fw-bold">{{ $post->title }}</small>
        </div>
    </div>
</div>
