<div>
    <div class="admin-page-header">
        <div>
            <a href="{{ route('posts.index') }}" class="small text-muted text-decoration-none" wire:navigate>
                <i class="bi bi-arrow-left me-1"></i> Posts
            </a>
            <h1 class="mt-1">{{ $post ? 'Edit post' : 'New post' }}</h1>
        </div>
        @if($post && ! $post->is_draft)
            <a href="{{ route('home') }}" target="_blank" class="btn btn-light">
                <i class="bi bi-box-arrow-up-right me-1"></i> View on site
            </a>
        @endif
    </div>

    <form wire:submit="save">
        <div class="row g-4">
            {{-- Main content --}}
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h2>Post type</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="row row-cols-3 row-cols-sm-4 row-cols-xl-5 g-2" role="radiogroup" aria-label="Post type">
                            @foreach($this->typesWithLabels as $typeOption)
                                <div class="col">
                                    <label @class(['admin-type-option h-100', 'active' => $type === $typeOption['value']])>
                                        <input type="radio" wire:model.live="type" value="{{ $typeOption['value'] }}"
                                            class="visually-hidden">
                                        <i class="bi {{ $typeOption['icon'] }}"></i>
                                        <span>{{ $typeOption['label'] }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('type') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Content</h2>
                    </div>
                    <div class="admin-card-body">
                        <x-dynamic-component :component="'posts.' . $type . '.form'" :type-data="$typeData" />
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="admin-sticky-actions">
                    <div class="admin-card mb-4">
                        <div class="admin-card-header d-flex align-items-center justify-content-between">
                            <h2>Publish</h2>
                            @if($post)
                                @if($post->is_draft)
                                    <span class="admin-badge is-draft">Draft</span>
                                @elseif($post->posted_at?->isFuture())
                                    <span class="admin-badge is-scheduled"><i class="bi bi-clock"></i>Scheduled</span>
                                @else
                                    <span class="admin-badge is-published">Published</span>
                                @endif
                            @endif
                        </div>
                        <div class="admin-card-body">
                            <div class="mb-3">
                                <label for="posted_at" class="form-label">Publish date</label>
                                <input type="datetime-local" id="posted_at" wire:model="posted_at" class="form-control">
                                <div class="form-text small">Set a future date to schedule the post.</div>
                                @error('posted_at') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="is_pinned" id="isPinned">
                                <label class="form-check-label" for="isPinned">Pin to the top of the feed</label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">{{ $post ? 'Update and publish' : 'Publish' }}</span>
                                    <span wire:loading wire:target="save">Saving…</span>
                                </button>
                                <button type="button" wire:click="saveDraft" class="btn btn-light" wire:loading.attr="disabled">
                                    Save as draft
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2>Details</h2>
                        </div>
                        <div class="admin-card-body">
                            <x-form.input name="tags" label="Tags" placeholder="tech, life, music" hint="Separate tags with commas." />

                            @if(in_array('cover', $handler->mediaCollections()))
                                <div class="mb-4">
                                    <label for="cover_image" class="form-label">Cover image</label>
                                    <input type="file" id="cover_image" wire:model="cover_image" class="form-control" accept="image/*">
                                    @error('cover_image') <span class="text-danger small">{{ $message }}</span> @enderror

                                    @if ($cover_image)
                                        <img src="{{ $cover_image->temporaryUrl() }}" alt="" class="img-fluid rounded mt-2">
                                    @elseif($post && $post->hasMedia('cover'))
                                        <div class="mt-2 text-muted small">Current image</div>
                                        <img src="{{ $post->getFirstMediaUrl('cover') }}" alt="" class="img-fluid rounded mt-1">
                                    @endif
                                </div>
                            @endif

                            @if(in_array('avatar', $handler->mediaCollections()))
                                <div class="mb-0">
                                    <label for="avatar_image" class="form-label">Profile picture</label>
                                    <input type="file" id="avatar_image" wire:model="avatar_image" class="form-control" accept="image/*">
                                    @error('avatar_image') <span class="text-danger small">{{ $message }}</span> @enderror

                                    @if ($avatar_image)
                                        <img src="{{ $avatar_image->temporaryUrl() }}" alt=""
                                            class="rounded-circle mt-2" style="width:80px; height:80px; object-fit:cover;">
                                    @elseif($post && $post->hasMedia('avatar'))
                                        <div class="mt-2 text-muted small">Current picture</div>
                                        <img src="{{ $post->getFirstMediaUrl('avatar') }}" alt=""
                                            class="rounded-circle mt-1" style="width:80px; height:80px; object-fit:cover;">
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
