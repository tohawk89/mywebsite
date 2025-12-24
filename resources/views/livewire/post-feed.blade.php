<div class="container text-center py-4">
    <div class="row g-2" id="masonry-grid" data-masonry='{"percentPosition": true }' wire:ignore.self>
        @foreach($posts as $post)
            @php
                $isDoubleWidth = in_array($post->type->value, ['IMAGE', 'YOUTUBE']);
                $colClass = $isDoubleWidth ? 'col-12 col-md-12 col-lg-8' : 'col-12 col-md-6 col-lg-4';
            @endphp
            <div class="{{ $colClass }} mb-2" wire:key="post-{{ $post->id }}">
                <x-post-card :post="$post" />
            </div>
        @endforeach
    </div>

    <div class="text-center my-5" x-data="{ intersect: false }" x-intersect="$wire.loadMore()">

        @if($posts->hasMorePages())
            <div wire:loading>
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        @else
            <div class="alert alert-light border-0 shadow-sm rounded-pill d-inline-block px-4 py-2 text-muted fw-medium">
                You've reached the end of the line! 🎉
            </div>
        @endif
    </div>

    @script
    <script>
        Livewire.hook('morph.updated', ({ el, component }) => {
            var grid = document.querySelector('#masonry-grid');
            var msnry = new Masonry(grid, {
                percentPosition: true
            });

            // Re-layout after images load to prevent overlap/gaps
            imagesLoaded(grid).on('progress', function () {
                msnry.layout();
            });
        });
    </script>
    @endscript
</div>