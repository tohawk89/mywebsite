<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" color="#90EE90" inset-media :excerpt="false">
    @if(isset($post->meta_data['spotify_url']))
        @php
            // Tracks and episodes use Spotify's compact player; albums, playlists, artists and shows need the tall one.
            $compact = preg_match('#/embed/(track|episode)/#', $post->meta_data['spotify_url']);
        @endphp
        <x-slot:media>
            <iframe wire:ignore style="border-radius: 0px;"
                src="{{ $post->meta_data['spotify_url'] }}"
                width="100%" height="{{ $compact ? 152 : 352 }}" frameBorder="0" allowfullscreen=""
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                loading="lazy" title="{{ $post->title }}"></iframe>
        </x-slot:media>
    @endif
</x-post-card>
