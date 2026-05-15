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
                            @foreach($this->typesWithLabels as $typeOption)
                                <label
                                    class="btn btn-sm rounded-0 {{ $type === $typeOption['value'] ? 'btn-dark' : 'btn-outline-secondary border-0 bg-light' }}"
                                    style="min-width: 100px;">
                                    <input type="radio" wire:model.live="type" value="{{ $typeOption['value'] }}"
                                        class="d-none">
                                    {{ $typeOption['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Title & Dynamic Fields -->
                <div class="card border-0 shadow-sm rounded-0 mb-4">
                    <div class="card-body p-4">

                        <x-dynamic-component
                            :component="'posts.' . $type . '.form'"
                            :type-data="$typeData"
                        />

                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-0 mb-4">
                    <div class="card-body p-4">
                    <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-dark rounded-0 py-2 fw-bold text-uppercase">
                                {{ $post ? 'Update & Publish' : 'Publish Post' }}
                            </button>
                            <button type="button" wire:click="saveDraft"
                                class="btn btn-outline-secondary rounded-0 py-2 fw-bold text-uppercase">
                                Save as Draft
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
                        @if(in_array('cover', $handler->mediaCollections()))
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

                        <!-- Avatar Image -->
                        @if(in_array('avatar', $handler->mediaCollections()))
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Profile Picture</label>
                                <input type="file" wire:model="avatar_image"
                                    class="form-control rounded-0 bg-light border-0">
                                @error('avatar_image') <span class="text-danger small">{{ $message }}</span> @enderror

                                @if ($avatar_image)
                                    <div class="mt-2">
                                        <img src="{{ $avatar_image->temporaryUrl() }}"
                                            class="rounded-circle mt-1" style="width:80px; height:80px; object-fit:cover;">
                                    </div>
                                @elseif($post && $post->hasMedia('avatar'))
                                    <div class="mt-2 text-muted small">Current Picture:</div>
                                    <img src="{{ $post->getFirstMediaUrl('avatar') }}"
                                        class="rounded-circle mt-1" style="width:80px; height:80px; object-fit:cover;">
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
