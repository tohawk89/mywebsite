<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" class="bg-white">
    @if($post->hasMedia('cover'))
        <x-slot:media>
            <img src="{{ $post->getFirstMediaUrl('cover') }}" class="card-img-top rounded-0" alt="{{ $post->title }}" loading="lazy">
        </x-slot:media>
    @endif

    <button class="btn btn-outline-dark btn-sm rounded-0 fw-medium w-100 mt-3"
        @click="$dispatch('open-modal', { id: {{ $post->id }} })">
        Read More
    </button>
</x-post-card>
