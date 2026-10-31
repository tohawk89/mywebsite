<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" class="bg-warning text-dark">
    <x-slot:body>
        @if($post->tags->isNotEmpty())
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-wrap gap-1">
                @foreach($post->tags as $tag)
                    <span class="badge bg-black text-warning fw-medium px-3 py-2 rounded-0">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        {{-- The form saves "Author / Source" as the post title. --}}
        @php($author = $post->hide_title ? null : ($post->title ?: ($post->meta_data['author'] ?? null)))
        <figure class="card-body text-center p-4 mb-0">
            <blockquote class="mb-0">
                <p class="fst-italic mb-0 quote-text">&ldquo;{{ $post->content }}&rdquo;</p>
            </blockquote>
            @if($author)
                <figcaption class="small fw-semibold mt-3">&mdash; {{ $author }}</figcaption>
            @endif
        </figure>
    </x-slot:body>
</x-post-card>
