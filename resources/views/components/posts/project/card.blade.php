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
</x-post-card>
