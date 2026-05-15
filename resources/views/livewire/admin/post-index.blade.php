<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Posts</h2>
        <a href="{{ route('posts.create') }}" class="btn btn-dark rounded-0 px-4" wire:navigate>
            <i class="bi bi-plus-lg me-2"></i>New Post
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50%;">Title</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Posted</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-semibold">{{ $post->title ?? 'No Title' }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;">
                                    {{ Str::limit(strip_tags($post->content), 80) }}
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge rounded-0 bg-light text-dark border border-secondary text-uppercase"
                                    style="font-size: 0.7rem;">
                                    @php
                                        $handlerClass = 'App\\PostTypes\\' . ucfirst($post->type) . '\\Handler';
                                        $typeHandler = class_exists($handlerClass) ? new $handlerClass : new \App\PostTypes\DefaultHandler($post->type);
                                    @endphp
                                    {{ $typeHandler->label() }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="small">{{ $post->posted_at?->format('M d, Y') ?? 'Draft' }}</div>
                                <div class="small text-muted">{{ $post->created_at->format('H:i') }}</div>
                            </td>
                            <td class="py-3">
                                @if($post->is_draft)
                                    <span class="badge rounded-0 bg-secondary text-white"><i class="bi bi-pencil me-1"></i>Draft</span>
                                @elseif($post->is_pinned)
                                    <span class="badge rounded-0 bg-warning text-dark"><i
                                            class="bi bi-pin-angle-fill me-1"></i>Pinned</span>
                                @else
                                    <span class="badge rounded-0 bg-success text-white">Published</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($post->is_pinned)
                                        <div class="btn-group btn-group-sm me-1">
                                            <button wire:click="moveUp({{ $post->id }})"
                                                class="btn btn-outline-secondary rounded-0"
                                                @disabled($post->id === $firstPinnedId)
                                                title="Move up">
                                                <i class="bi bi-chevron-up"></i>
                                            </button>
                                            <button wire:click="moveDown({{ $post->id }})"
                                                class="btn btn-outline-secondary rounded-0"
                                                @disabled($post->id === $lastPinnedId)
                                                title="Move down">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <div class="btn-group">
                                        <a href="{{ route('posts.edit', $post) }}"
                                            class="btn btn-sm btn-outline-dark rounded-0 border-end-0" wire:navigate>
                                            Edit
                                        </a>
                                        <button wire:click="delete({{ $post->id }})"
                                            wire:confirm="Are you sure you want to delete this post?"
                                            class="btn btn-sm btn-outline-danger rounded-0">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No posts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>
