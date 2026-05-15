<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-12 col-lg-8 mb-2">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start h-100" style="background-color: #FFCCCB;">
        @if(isset($post->meta_data['embed_url']))
            <div class="ratio ratio-16x9">
                <iframe wire:ignore
                    src="{{ $post->meta_data['embed_url'] }}"
                    title="{{ $post->title }}" allowfullscreen class="rounded-0"></iframe>
            </div>
        @endif

        <div class="card-body">
            <x-ui.post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>
    </div>
</div>
