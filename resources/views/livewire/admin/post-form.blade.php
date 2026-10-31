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

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="is_pinned" id="isPinned">
                                <label class="form-check-label" for="isPinned">Pin to the top of the feed</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="hide_title" id="hideTitle">
                                <label class="form-check-label" for="hideTitle">Hide the title on the site</label>
                                <div class="form-text small">It still shows here in the admin.</div>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="hide_date" id="hideDate">
                                <label class="form-check-label" for="hideDate">Hide the date on this card</label>
                                @unless(config('site.show_dates'))
                                    <div class="form-text small">Dates are off for every card in Site settings &rsaquo; Feed.</div>
                                @endunless
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
                            <x-form.tags name="tags" :suggestions="$this->allTagNames"
                                hint="Pick an existing tag or type a new one, then press Enter.">
                                @if(\App\Support\AiWriting::enabled())
                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                        <button type="button" class="btn btn-light btn-sm ai-menu-toggle mb-0" wire:click="suggestTags"
                                            wire:loading.attr="disabled" wire:target="suggestTags">
                                            <span wire:loading.remove wire:target="suggestTags"><i class="bi bi-stars"></i> Suggest tags</span>
                                            <span wire:loading wire:target="suggestTags"><span class="spinner-border spinner-border-sm"></span> Thinking…</span>
                                        </button>
                                        @foreach($aiTags as $suggestedTag)
                                            <button type="button" class="btn btn-sm tag-suggestion" wire:click="addSuggestedTag(@js($suggestedTag))"
                                                wire:key="ai-tag-{{ $loop->index }}">
                                                <i class="bi bi-plus"></i>{{ $suggestedTag }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </x-form.tags>

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

    @if($aiError)
        <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
            <div class="toast show align-items-center border-0 shadow" role="alert">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-circle-fill text-danger me-2"></i>{{ $aiError }}</div>
                    <button type="button" class="btn-close me-2 m-auto" aria-label="Close" wire:click="$set('aiError', null)"></button>
                </div>
            </div>
        </div>
    @endif

    {{-- Review an AI rewrite or pick a title idea before anything changes --}}
    @if($aiSuggestion || $aiTitles)
        <div class="modal d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="aiReviewTitle"
            x-data @keydown.escape.window="$wire.discardAi()">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow" style="border-radius: 0.75rem;">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5 fw-semibold" id="aiReviewTitle">
                            <i class="bi bi-stars text-primary me-1"></i>
                            {{ $aiSuggestion ? \App\Ai\Agents\WritingAssistant::ACTIONS[$aiSuggestion['action']][0] : 'Title ideas' }}
                        </h2>
                        <button type="button" class="btn-close" aria-label="Discard" wire:click="discardAi"></button>
                    </div>
                    <div class="modal-body">
                        @if($aiSuggestion)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small fw-semibold text-muted mb-1">Your text</div>
                                    <div class="ai-review-text">{{ $aiSuggestion['original'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small fw-semibold text-primary mb-1">Suggestion</div>
                                    <div class="ai-review-text is-suggestion" wire:loading.class="opacity-50" wire:target="retryAi">{{ $aiSuggestion['result'] }}</div>
                                </div>
                            </div>
                        @else
                            <div class="d-grid gap-2">
                                @foreach($aiTitles as $index => $idea)
                                    <button type="button" class="btn btn-light text-start" wire:click="useTitle({{ $index }})">
                                        {{ $idea }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <span class="small text-muted me-auto">Nothing changes until you accept.</span>
                        @if($aiSuggestion)
                            <button type="button" class="btn btn-light" wire:click="discardAi">Discard</button>
                            <button type="button" class="btn btn-light" wire:click="retryAi" wire:loading.attr="disabled" wire:target="retryAi">
                                <span wire:loading.remove wire:target="retryAi"><i class="bi bi-arrow-repeat me-1"></i>Try again</span>
                                <span wire:loading wire:target="retryAi">Thinking…</span>
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="acceptAi">Accept</button>
                        @else
                            <button type="button" class="btn btn-light" wire:click="discardAi">Close</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
