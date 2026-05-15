<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start h-100" style="background-color: #FFE4D6;">
        @if(isset($post->meta_data['instagram_embed_url']))
            <div class="p-3 pb-0">
                <iframe wire:ignore
                    src="{{ $post->meta_data['instagram_embed_url'] }}"
                    width="100%" height="560" frameborder="0" scrolling="no"
                    allowtransparency="true" loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        @endif

        <div class="card-body">
            <x-post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>
    </div>
</div>
