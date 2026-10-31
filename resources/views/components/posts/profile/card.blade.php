<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<x-post-card :post="$post" color="#E6E6FA">
    <x-slot:body>
        <div class="card-body text-center p-4">
            <x-ui.post-tags :tags="$post->tags" />

            <div class="mb-3">
                @if($post->hasMedia('avatar'))
                    <img src="{{ $post->getFirstMediaUrl('avatar') }}" alt="{{ $post->title }}"
                        class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center shadow-sm text-white"
                        style="width: 100px; height: 100px; font-size: 2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif
            </div>

            @if($post->publicTitle())
                <h4 class="card-title fw-bold font-sans mb-1">{{ $post->publicTitle() }}</h4>
            @endif

            @if($post->content)
                <p class="card-text text-muted mb-4 font-serif">{{ $post->content }}</p>
            @endif

            @if(!empty($post->meta_data['sns']))
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @foreach($post->meta_data['sns'] as $link)
                        @php $snsType = \App\Enums\SnsType::tryFrom($link['platform']); @endphp
                        @if($snsType && !empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                                class="text-dark fs-4 text-decoration-none opacity-75"
                                title="{{ $snsType->label() }}">
                                <i class="bi {{ $snsType->icon() }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </x-slot:body>
</x-post-card>
