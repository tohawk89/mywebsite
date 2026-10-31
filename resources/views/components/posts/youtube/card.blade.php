<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="feed-item">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start" style="background-color: #FFCCCB;">
        @php
            $videoId = $post->meta_data['video_id']
                ?? \App\PostTypes\Youtube\Handler::videoId($post->meta_data['youtube_url'] ?? '');
        @endphp
        @if($videoId)
            {{-- Show the thumbnail until clicked so the heavy player only loads on demand. --}}
            <div class="ratio ratio-16x9 bg-dark" wire:ignore x-data="{ playing: false }">
                <template x-if="playing">
                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1"
                        title="{{ $post->title }}"
                        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                        allowfullscreen class="border-0"></iframe>
                </template>
                <button type="button" x-show="! playing" @click="playing = true"
                    class="btn p-0 border-0 rounded-0 youtube-facade"
                    aria-label="Play {{ $post->title }}">
                    <img src="https://i.ytimg.com/vi/{{ $videoId }}/hqdefault.jpg"
                        class="w-100 h-100 object-fit-cover" alt="" loading="lazy">
                    <i class="bi bi-youtube youtube-facade-icon"></i>
                </button>
            </div>
        @endif

        <div class="card-body">
            <x-ui.post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>
    </div>
</div>
