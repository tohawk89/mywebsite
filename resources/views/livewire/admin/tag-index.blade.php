<div style="max-width: 900px;">
    <div class="admin-page-header">
        <div>
            <h1>Tags</h1>
            <p>Rename a tag to the name of another to merge them.</p>
        </div>
        @if($unusedCount > 0)
            <button type="button" class="btn btn-light" wire:click="deleteUnused"
                wire:confirm="Delete {{ $unusedCount }} tags that aren't on any post?">
                <i class="bi bi-trash me-1"></i> Delete {{ $unusedCount }} unused
            </button>
        @endif
    </div>

    <div class="admin-card mb-3">
        <div class="admin-card-body">
            <div class="admin-search mw-100">
                <i class="bi bi-search"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search tags" aria-label="Search tags">
            </div>
        </div>
    </div>

    <div class="admin-card overflow-hidden">
        <table class="table admin-table table-hover">
            <thead>
                <tr>
                    <th class="ps-3">Tag</th>
                    <th>Posts</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tags as $tag)
                    <tr wire:key="tag-{{ $tag->id }}">
                        <td class="ps-3">
                            @if($editingId === $tag->id)
                                <form wire:submit="rename" class="d-flex gap-2" style="max-width: 360px;">
                                    <input type="text" wire:model="editingName" class="form-control form-control-sm" autofocus
                                        aria-label="New name for {{ $tag->name }}" @keydown.escape="$wire.cancelEdit()">
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="button" class="btn btn-light btn-sm" wire:click="cancelEdit">Cancel</button>
                                </form>
                                @error('editingName') <span class="text-danger small">{{ $message }}</span> @enderror
                            @else
                                <span class="tag-chip pe-2">{{ $tag->name }}</span>
                                <span class="small text-muted ms-1">{{ $tag->slug }}</span>
                            @endif
                        </td>
                        <td>
                            @if($tag->posts_count > 0)
                                <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}" wire:navigate class="text-decoration-none">
                                    {{ $tag->posts_count }} {{ str('post')->plural($tag->posts_count) }}
                                </a>
                            @else
                                <span class="text-muted">Unused</span>
                            @endif
                        </td>
                        <td class="text-end pe-3 text-nowrap">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-light" wire:click="edit({{ $tag->id }})" title="Rename" aria-label="Rename {{ $tag->name }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-light text-danger" wire:click="delete({{ $tag->id }})"
                                    wire:confirm="Delete the tag {{ $tag->name }}? It will be removed from {{ $tag->posts_count }} {{ str('post')->plural($tag->posts_count) }}."
                                    title="Delete" aria-label="Delete {{ $tag->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            {{ $search !== '' ? 'No tags match your search.' : 'No tags yet. Add tags when you write a post.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tags->hasPages())
        <div class="mt-3">{{ $tags->links() }}</div>
    @endif
</div>
