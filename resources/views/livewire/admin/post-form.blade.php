<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            {{ $post ? 'Edit Post' : 'Create Post' }}
        </h2>
        <a href="{{ route('posts.index') }}" class="btn btn-outline-dark rounded-0 px-4" wire:navigate>
            Cancel
        </a>
    </div>

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Type Selector -->
                <div class="card border-0 shadow-sm rounded-0 mb-4">
                    <div class="card-body">
                        <label class="form-label fw-bold small text-uppercase">Post Type</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($types as $enumType)
                                <label
                                    class="btn btn-sm rounded-0 {{ $type === $enumType->value ? 'btn-dark' : 'btn-outline-secondary border-0 bg-light' }}"
                                    style="min-width: 100px;">
                                    <input type="radio" wire:model.live="type" value="{{ $enumType->value }}"
                                        class="d-none">
                                    {{ $enumType->label() }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Title & Dynamic Fields -->
                <div class="card border-0 shadow-sm rounded-0 mb-4">
                    <div class="card-body p-4">

                        <!-- Title (Common) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">
                                {{ $type === 'quote' ? 'Author / Source' : 'Title' }}
                            </label>
                            <input type="text" wire:model="title"
                                class="form-control rounded-0 form-control-lg bg-light border-0">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Blog Content -->
                        @if($type === 'blog' || $type === 'page' || $type === 'project')
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Content</label>
                                <textarea wire:model="content" class="form-control rounded-0 bg-light border-0"
                                    rows="12"></textarea>
                                @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Quote Content -->
                        @if($type === 'quote')
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Quote Text</label>
                                <textarea wire:model="content" class="form-control rounded-0 bg-light border-0" rows="4"
                                    placeholder="Enter the quote here..."></textarea>
                                @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Spotify URL -->
                        @if($type === 'spotify')
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Spotify Embed URL</label>
                                <input type="url" wire:model="spotify_url" class="form-control rounded-0 bg-light border-0"
                                    placeholder="https://open.spotify.com/track/...">
                                @error('spotify_url') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- YouTube URL -->
                        @if($type === 'youtube')
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">YouTube Video URL</label>
                                <input type="url" wire:model="youtube_url" class="form-control rounded-0 bg-light border-0"
                                    placeholder="https://www.youtube.com/watch?v=...">
                                @error('youtube_url') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-dark rounded-0 py-2 fw-bold text-uppercase">
                                {{ $post ? 'Update Post' : 'Publish Post' }}
                            </button>
                        </div>

                        <!-- Published At -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Date</label>
                            <input type="datetime-local" wire:model="posted_at"
                                class="form-control rounded-0 bg-light border-0">
                            @error('posted_at') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Pinned -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input rounded-0" type="checkbox" wire:model="is_pinned"
                                    id="isPinned">
                                <label class="form-check-label" for="isPinned">
                                    Pin this post
                                </label>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Tags</label>
                            <input type="text" wire:model="tags" class="form-control rounded-0 bg-light border-0"
                                placeholder="tech, life, music">
                            <div class="form-text small">Comma separated</div>
                        </div>

                        <!-- Cover Image -->
                        @if($type === 'blog' || $type === 'image' || $type === 'project')
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Cover Image</label>
                                <input type="file" wire:model="cover_image"
                                    class="form-control rounded-0 bg-light border-0">
                                @error('cover_image') <span class="text-danger small">{{ $message }}</span> @enderror

                                @if ($cover_image)
                                    <div class="mt-2">
                                        <img src="{{ $cover_image->temporaryUrl() }}" class="img-fluid w-100">
                                    </div>
                                @elseif($post && $post->hasMedia('cover'))
                                    <div class="mt-2 text-muted small">Current Image:</div>
                                    <img src="{{ $post->getFirstMediaUrl('cover') }}" class="img-fluid w-100 mt-1">
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>