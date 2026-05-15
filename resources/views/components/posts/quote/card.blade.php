<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card rounded-0 border-0 shadow-sm h-100 bg-warning text-dark">
        @if($post->tags->isNotEmpty())
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-wrap gap-1">
                @foreach($post->tags as $tag)
                    <span class="badge bg-black text-warning fw-medium px-3 py-2 rounded-0">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        <div class="card-body d-flex flex-column justify-content-center align-items-center p-4">
            <blockquote class="blockquote mb-0 text-center">
                <p class="fs-4 fst-italic fw-serif">"{{ $post->content }}"</p>
                @if(isset($post->meta_data['author']))
                    <footer class="blockquote-footer mt-3 text-dark opacity-75">{{ $post->meta_data['author'] }}</footer>
                @endif
            </blockquote>
        </div>
    </div>
</div>
