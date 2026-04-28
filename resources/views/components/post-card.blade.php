@props(['post'])

@use('App\Enums\PostType')
@use('App\Enums\SnsType')

<div class="card border-0 h-100 shadow-sm rounded-0 overflow-hidden text-start post-card group" style="background-color: {{
         match ($post->type) {
        PostType::SPOTIFY => '#90EE90',
        PostType::YOUTUBE => '#FFCCCB',
    PostType::INSTAGRAM => '#FFE4D6',
        PostType::QUOTE => '#FFFFE0',
        PostType::PROFILE => '#E6E6FA', // Lavender for Profile
        default => '#ffffff'
    }
     }};">

    {{-- PROFILE CARD --}}
    @if($post->type === PostType::PROFILE)
        <div class="card-body text-center p-4">
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
            <h4 class="card-title fw-bold font-sans mb-1">{{ $post->title }}</h4>
            @if($post->content)
                <div class="card-text text-muted mb-4 font-serif">{{ $post->content }}</div>
            @endif

            @if(!empty($post->meta_data['sns']))
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @foreach($post->meta_data['sns'] as $link)
                        @php
                            $snsType = SnsType::tryFrom($link['platform']);
                        @endphp
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

        {{-- IMAGE CARD --}}
    @elseif($post->type === PostType::IMAGE)
        <div class="position-relative h-100" style="min-height: 300px; cursor: pointer;"
            @click="$dispatch('open-modal', { id: {{ $post->id }} })">
            @if($post->hasMedia('cover'))
                <img src="{{ $post->getFirstMediaUrl('cover') }}"
                    class="img-fluid w-100 h-100 object-fit-cover position-absolute top-0 start-0" alt="{{ $post->title }}">
            @else
                <div
                    class="w-100 h-100 position-absolute top-0 start-0 bg-secondary d-flex align-items-center justify-content-center text-white">
                    <i class="bi bi-image fs-1"></i>
                </div>
            @endif
            <div
                class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-to-t from-black/50 to-transparent text-white opacity-0 group-hover-opacity-100 transition-opacity">
                <small class="fw-bold">{{ $post->title }}</small>
            </div>
        </div>

        {{-- YOUTUBE CARD --}}
    @elseif($post->type === PostType::YOUTUBE)
        @if(isset($post->meta_data['embed_url']))
            <div class="ratio ratio-16x9">
                <iframe src="{{ $post->meta_data['embed_url'] }}" title="{{ $post->title }}" allowfullscreen
                    class="rounded-0"></iframe>
            </div>
        @endif
        <div class="card-body">
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>

        {{-- SPOTIFY CARD --}}
    @elseif($post->type === PostType::SPOTIFY)
        @if(isset($post->meta_data['spotify_url']))
            <div class="p-3 pb-0">
                <iframe style="border-radius:0px" src="{{ $post->meta_data['spotify_url'] }}" width="100%" height="152"
                    frameBorder="0" allowfullscreen=""
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"
                    title="{{ $post->title }}"></iframe>
            </div>
        @endif
        <div class="card-body">
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>

        {{-- INSTAGRAM CARD --}}
    @elseif($post->type === PostType::INSTAGRAM)
        @if(isset($post->meta_data['instagram_embed_url']))
            <div class="p-3 pb-0">
                <iframe src="{{ $post->meta_data['instagram_embed_url'] }}" width="100%" height="560" frameborder="0"
                    scrolling="no" allowtransparency="true" loading="lazy" title="{{ $post->title }}"></iframe>
            </div>
        @endif
        <div class="card-body">
            <x-post-tags :tags="$post->tags" />
            <h5 class="card-title fw-bold font-sans mb-0">{{ $post->title }}</h5>
        </div>

        {{-- BLOG / QUOTE / PAGE / PROJECT --}}
    @else
        {{-- Cover Image --}}
        @if($post->hasMedia('cover'))
            <img src="{{ $post->getFirstMediaUrl('cover') }}" class="card-img-top rounded-0" alt="{{ $post->title }}">
        @endif

        <div class="card-body d-flex flex-column">
            <x-post-tags :tags="$post->tags" />

            <h5 class="card-title fw-bold font-sans">{{ $post->title }}</h5>

            @if($post->type === PostType::QUOTE)
                <blockquote class="blockquote mb-0 font-serif fst-italic">
                    <p>"{{ $post->content }}"</p>
                    @if(isset($post->meta_data['author']))
                        <footer class="blockquote-footer mt-2"><cite title="Source">{{ $post->meta_data['author'] }}</cite></footer>
                    @endif
                </blockquote>
            @else
                <p class="card-text text-muted flex-grow-1 font-serif line-clamp-3">{{ Str::limit($post->content, 150) }}</p>
            @endif

            {{-- Read More Button (Only for Blog) --}}
            @if($post->type === PostType::BLOG)
                <div class="mt-3">
                    <button class="btn btn-outline-dark btn-sm rounded-0 fw-medium w-100"
                        @click="$dispatch('open-modal', { id: {{ $post->id }} })">
                        Read More
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
