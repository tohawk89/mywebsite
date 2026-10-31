<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="feed-item">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start" style="background-color: #FFE4D6;">
        @if(isset($post->meta_data['instagram_embed_url']))
            {{-- Captioned embed; starts at a typical height and app.js resizes it to the height Instagram reports. --}}
            <div class="p-3 pb-0" wire:ignore>
                <iframe data-instagram-embed
                    src="{{ \App\PostTypes\Instagram\Handler::captionedEmbedUrl($post->meta_data['instagram_embed_url']) }}"
                    width="100%" height="560" frameborder="0" scrolling="no"
                    allowtransparency="true" loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        @endif

        @if($post->title || $post->content || $post->tags->isNotEmpty())
            <div class="card-body">
                <x-ui.post-tags :tags="$post->tags" />
                @if($post->title)
                    <h5 class="card-title fw-bold font-sans">{{ $post->title }}</h5>
                @endif
                @if($post->content)
                    <p class="card-text text-muted flex-grow-1 font-serif line-clamp-3">{{ $post->content }}</p>
                @endif
            </div>
        @else
            <div class="pb-3"></div>
        @endif
    </div>
</div>
