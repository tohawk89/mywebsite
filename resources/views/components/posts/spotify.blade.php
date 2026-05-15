<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start h-100" style="background-color: #90EE90;">
        @if(isset($post->meta_data['spotify_url']))
            <div class="p-3 pb-0">
                <iframe wire:ignore style="border-radius:0px"
                    src="{{ $post->meta_data['spotify_url'] }}"
                    width="100%" height="152" frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                    loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        @endif

        <div class="card-body">
            <x-post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>
    </div>
</div>
