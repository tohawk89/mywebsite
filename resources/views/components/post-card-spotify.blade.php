@props(['post'])

<div class="card rounded-0 border-0 shadow-sm h-100 bg-success text-white">
    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-wrap gap-1">
            @foreach($post->tags as $tag)
                <span class="badge bg-white text-success fw-medium px-3 py-2 rounded-0">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="p-3">
        @if(isset($post->meta_data['url']))
            <iframe style="border-radius:0px"
                src="https://open.spotify.com/embed/album/{{ basename($post->meta_data['url']) }}?utm_source=generator&theme=0"
                width="100%" height="80" frameBorder="0" allowfullscreen=""
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
        @endif
    </div>

    @if($post->title)
        <div class="card-body pt-0">
            <h5 class="card-title fw-bold">{{ $post->title }}</h5>
        </div>
    @endif
</div>