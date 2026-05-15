<?php
use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start h-100 bg-white">
        @if($post->hasMedia('cover'))
            <img src="{{ $post->getFirstMediaUrl('cover') }}" class="card-img-top rounded-0" alt="{{ $post->title }}">
        @endif

        <div class="card-body d-flex flex-column">
            <x-post-tags :tags="$post->tags" />

            <h5 class="card-title fw-bold font-sans">{{ $post->title }}</h5>

            @if($post->content)
                <p class="card-text text-muted flex-grow-1 font-serif line-clamp-3">{{ Str::limit($post->content, 150) }}</p>
            @endif
        </div>
    </div>
</div>
