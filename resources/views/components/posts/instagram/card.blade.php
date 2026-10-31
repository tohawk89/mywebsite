<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" color="#FFE4D6" inset-media>
    @if(isset($post->meta_data['instagram_embed_url']))
        <x-slot:media>
            {{-- Captioned embed; starts at a typical height and app.js resizes it to the height Instagram reports. --}}
            <iframe data-instagram-embed wire:ignore
                src="{{ \App\PostTypes\Instagram\Handler::captionedEmbedUrl($post->meta_data['instagram_embed_url']) }}"
                width="100%" height="560" frameborder="0" scrolling="no"
                allowtransparency="true" loading="lazy" title="{{ $post->title }}"></iframe>
        </x-slot:media>
    @endif
</x-post-card>
