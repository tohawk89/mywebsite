<div>
    <div class="admin-page-header">
        <div>
            <h1>Posts</h1>
            <p>Everything on your feed, including drafts and scheduled posts.</p>
        </div>
        <a href="{{ route('posts.create') }}" class="btn btn-primary" wire:navigate>
            <i class="bi bi-plus-lg me-1"></i> New post
        </a>
    </div>

    {{-- Filters --}}
    <div class="admin-card mb-3">
        <div class="admin-card-body d-flex flex-wrap gap-2 align-items-center">
            <div class="admin-search flex-grow-1">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                    placeholder="Search title or content" aria-label="Search posts">
            </div>

            <select wire:model.live="type" class="form-select w-auto" aria-label="Filter by type">
                <option value="">All types</option>
                @foreach($this->types as $value => $typeInfo)
                    <option value="{{ $value }}">{{ $typeInfo['label'] }}</option>
                @endforeach
            </select>

            <div class="admin-filter-pills" role="group" aria-label="Filter by status">
                <button type="button" wire:click="$set('status', '')" @class(['btn btn-light', 'active' => $status === ''])>All</button>
                @foreach(\App\Livewire\Admin\PostIndex::STATUSES as $value => $label)
                    <button type="button" wire:click="$set('status', '{{ $value }}')"
                        @class(['btn btn-light', 'active' => $status === $value])>{{ $label }}</button>
                @endforeach
            </div>

            @if($tagName)
                <span class="tag-chip">
                    <i class="bi bi-tag"></i> {{ $tagName }}
                    <button type="button" wire:click="$set('tag', '')" aria-label="Remove tag filter">&times;</button>
                </span>
            @endif

            @if($filtering)
                <button type="button" wire:click="clearFilters" class="btn btn-link btn-sm text-decoration-none">
                    Clear filters
                </button>
            @endif
        </div>
    </div>

    {{-- Bulk actions --}}
    @if(count($selected) > 0)
        <div class="admin-card mb-3">
            <div class="admin-card-body py-2 d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-semibold me-2">{{ count($selected) }} selected</span>
                <button type="button" wire:click="bulk('publish')" class="btn btn-light btn-sm">
                    <i class="bi bi-send me-1"></i> Publish
                </button>
                <button type="button" wire:click="bulk('draft')" class="btn btn-light btn-sm">
                    <i class="bi bi-pencil me-1"></i> Move to drafts
                </button>
                <button type="button" wire:click="bulk('delete')"
                    wire:confirm="Delete {{ count($selected) }} selected posts? This can't be undone."
                    class="btn btn-light btn-sm text-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
                <button type="button" wire:click="$set('selected', [])" class="btn btn-link btn-sm text-decoration-none ms-auto">
                    Clear selection
                </button>
            </div>
        </div>
    @endif

    <div class="admin-card overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table table-hover">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 2.5rem;">
                            <input type="checkbox" class="form-check-input" aria-label="Select all posts on this page"
                                x-data
                                x-on:change="$wire.set('selected', $event.target.checked ? @js($posts->pluck('id')->map(fn ($id) => (string) $id)->values()) : [])"
                                @checked($posts->isNotEmpty() && count($selected) === $posts->count())>
                        </th>
                        <th>Post</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        @php
                            $typeInfo = $this->types[$post->type] ?? ['label' => ucfirst($post->type), 'icon' => 'bi-file-earmark'];
                            $thumb = $post->getFirstMediaUrl('cover') ?: $post->getFirstMediaUrl('avatar');
                            $isScheduled = ! $post->is_draft && $post->posted_at?->isFuture();
                        @endphp
                        <tr wire:key="post-row-{{ $post->id }}">
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input" wire:model.live="selected"
                                    value="{{ $post->id }}" aria-label="Select {{ $post->title ?: 'post' }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="" class="admin-thumb" loading="lazy">
                                    @else
                                        <span class="admin-thumb"><i class="bi {{ $typeInfo['icon'] }}"></i></span>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('posts.edit', $post) }}" wire:navigate
                                            class="fw-semibold text-body text-decoration-none d-block text-truncate" style="max-width: 320px;">
                                            {{ $post->title ?: 'Untitled' }}
                                        </a>
                                        <div class="small text-muted text-truncate" style="max-width: 320px;">
                                            {{ $post->excerpt(70) ?: ' ' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap small">
                                <i class="bi {{ $typeInfo['icon'] }} text-muted me-1"></i>{{ $typeInfo['label'] }}
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($post->is_draft)
                                        <span class="admin-badge is-draft">Draft</span>
                                    @elseif($isScheduled)
                                        <span class="admin-badge is-scheduled"><i class="bi bi-clock"></i>Scheduled</span>
                                    @else
                                        <span class="admin-badge is-published">Published</span>
                                    @endif
                                    @if($post->is_pinned)
                                        <span class="admin-badge is-pinned"><i class="bi bi-pin-angle-fill"></i>Pinned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="small text-nowrap">
                                <div>{{ $post->posted_at?->format('M j, Y') ?? '—' }}</div>
                                <div class="text-muted">{{ $post->posted_at?->format('H:i') }}</div>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($post->is_pinned)
                                    <div class="btn-group btn-group-sm me-1">
                                        <button wire:click="moveUp({{ $post->id }})" class="btn btn-light"
                                            @disabled($post->id === $firstPinnedId) title="Move up" aria-label="Move up">
                                            <i class="bi bi-chevron-up"></i>
                                        </button>
                                        <button wire:click="moveDown({{ $post->id }})" class="btn btn-light"
                                            @disabled($post->id === $lastPinnedId) title="Move down" aria-label="Move down">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                @endif
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="togglePin({{ $post->id }})" class="btn btn-light"
                                        title="{{ $post->is_pinned ? 'Unpin' : 'Pin' }}" aria-label="{{ $post->is_pinned ? 'Unpin' : 'Pin' }}">
                                        <i @class(['bi', 'bi-pin-angle-fill text-warning' => $post->is_pinned, 'bi-pin-angle' => ! $post->is_pinned])></i>
                                    </button>
                                    <button wire:click="togglePublished({{ $post->id }})" class="btn btn-light"
                                        title="{{ $post->is_draft ? 'Publish' : 'Move to drafts' }}"
                                        aria-label="{{ $post->is_draft ? 'Publish' : 'Move to drafts' }}">
                                        <i @class(['bi', 'bi-send' => $post->is_draft, 'bi-eye-slash' => ! $post->is_draft])></i>
                                    </button>
                                    <a href="{{ route('posts.edit', $post) }}" class="btn btn-light" wire:navigate
                                        title="Edit" aria-label="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="delete({{ $post->id }})"
                                        wire:confirm="Delete this post? This can't be undone."
                                        class="btn btn-light text-danger" title="Delete" aria-label="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                @if($filtering)
                                    <div class="fw-semibold mb-1">No posts match these filters</div>
                                    <button type="button" wire:click="clearFilters" class="btn btn-light btn-sm mt-2">Clear filters</button>
                                @else
                                    <div class="fw-semibold mb-1">Write your first post</div>
                                    <div class="text-muted small mb-3">Posts you create show up on your feed.</div>
                                    <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm" wire:navigate>New post</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($posts->hasPages())
        <div class="mt-3">{{ $posts->links() }}</div>
    @endif
</div>
