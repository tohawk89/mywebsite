@props(['post'])

<div class="card rounded-0 border-0 shadow-sm h-100 bg-danger text-white">
    {{-- Tags --}}
    @if($post->tags->isNotEmpty())
        <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex flex-wrap gap-1">
            @foreach($post->tags as $tag)
                <span class="badge bg-white text-danger fw-medium px-3 py-2 rounded-0">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="ratio ratio-16x9 rounded-0">
        @if(isset($post->meta_data['video_id']))
            <iframe src="https://www.youtube.com/embed/{{ $post->meta_data['video_id'] }}" title="YouTube video"
                allowfullscreen></iframe>
        @endif
    </div>

    @if($post->title)
        <div class="card-body">
            <h5 class="card-title fw-bold">{{ $post->title }}</h5>
        </div>
    @endif
</div>