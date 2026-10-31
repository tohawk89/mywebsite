<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="feed-item">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start" style="background-color: #90EE90;">
        @if(isset($post->meta_data['spotify_url']))
            @php
                // Tracks and episodes use Spotify's compact player; albums, playlists, artists and shows need the tall one.
                $compact = preg_match('#/embed/(track|episode)/#', $post->meta_data['spotify_url']);
            @endphp
            <div class="p-3 pb-0" wire:ignore>
                <iframe style="border-radius: 0px;"
                    src="{{ $post->meta_data['spotify_url'] }}"
                    width="100%" height="{{ $compact ? 152 : 352 }}" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        @endif

        <div class="card-body">
            <x-ui.post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>
    </div>
</div>
