<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card border-0 shadow-sm rounded-0 overflow-hidden text-start h-100" style="background-color: #E8F5E9;">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                @if(!empty($post->meta_data['owner_avatar']))
                    <img src="{{ $post->meta_data['owner_avatar'] }}" alt="owner"
                        class="rounded-circle flex-shrink-0" style="width:28px; height:28px; object-fit:cover;">
                @endif
                <a href="{{ $post->meta_data['repo_url'] }}" target="_blank" rel="noopener noreferrer"
                    class="fw-bold text-dark text-decoration-none font-sans">
                    {{ $post->meta_data['repo_name'] ?? $post->meta_data['repo_url'] }}
                </a>
                <i class="bi bi-box-arrow-up-right small text-muted ms-auto flex-shrink-0"></i>
            </div>

            @if(!empty($post->meta_data['repo_description']))
                <p class="text-muted small mb-2 font-serif">{{ $post->meta_data['repo_description'] }}</p>
            @endif

            <div class="d-flex align-items-center gap-3 flex-wrap small text-muted mb-2">
                @if(!empty($post->meta_data['language']))
                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">{{ $post->meta_data['language'] }}</span>
                @endif
                @if(isset($post->meta_data['stars']))
                    <span><i class="bi bi-star me-1"></i>{{ number_format($post->meta_data['stars']) }}</span>
                @endif
                @if(isset($post->meta_data['forks']))
                    <span><i class="bi bi-diagram-2 me-1"></i>{{ number_format($post->meta_data['forks']) }}</span>
                @endif
            </div>

            @if(!empty($post->meta_data['topics']))
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach($post->meta_data['topics'] as $topic)
                        <span class="badge rounded-pill bg-light text-secondary border small">{{ $topic }}</span>
                    @endforeach
                </div>
            @endif

            @if(!empty($post->meta_data['title']) || !empty($post->meta_data['note']))
                <hr class="my-2">
                @if(!empty($post->meta_data['title']))
                    <p class="fw-semibold mb-1 small font-sans">{{ $post->meta_data['title'] }}</p>
                @endif
                @if(!empty($post->meta_data['note']))
                    <p class="text-muted small mb-0 font-serif">{{ $post->meta_data['note'] }}</p>
                @endif
            @endif
        </div>
    </div>
</div>
