<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\PostTypes\PostTypeRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin')]
#[Title('Posts')]
class PostIndex extends Component
{
    use WithPagination;

    /** Statuses the list can be filtered by, with their labels. */
    public const STATUSES = [
        'published' => 'Published',
        'scheduled' => 'Scheduled',
        'draft' => 'Drafts',
        'pinned' => 'Pinned',
    ];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $status = '';

    /** Tag slug, e.g. from the Tags page. */
    #[Url(except: '')]
    public string $tag = '';

    /** @var array<int, string> IDs of posts ticked for a bulk action. */
    public array $selected = [];

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'type', 'status', 'tag'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'type', 'status', 'tag', 'selected');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [(string) $id]));
        $this->dispatch('notify', message: 'Post deleted.');
    }

    public function togglePin(int $id): void
    {
        $post = Post::findOrFail($id);
        $pinning = ! $post->is_pinned;

        $post->update([
            'is_pinned' => $pinning,
            'sort_order' => $pinning ? (Post::where('is_pinned', true)->max('sort_order') ?? -1) + 1 : 0,
        ]);

        $this->dispatch('notify', message: $pinning ? 'Post pinned.' : 'Post unpinned.');
    }

    public function togglePublished(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->update([
            'is_draft' => ! $post->is_draft,
            'posted_at' => $post->posted_at ?? now(),
        ]);

        $this->dispatch('notify', message: $post->is_draft ? 'Moved to drafts.' : 'Post published.');
    }

    public function bulk(string $action): void
    {
        $ids = array_map('intval', $this->selected);

        if ($ids === [] || ! in_array($action, ['publish', 'draft', 'delete'], true)) {
            return;
        }

        if ($action === 'delete') {
            Post::whereIn('id', $ids)->get()->each->delete();
        } else {
            Post::whereIn('id', $ids)->update(['is_draft' => $action === 'draft']);
            Post::whereIn('id', $ids)->whereNull('posted_at')->update(['posted_at' => now()]);
        }

        $count = count($ids);
        $noun = str('post')->plural($count);
        $this->selected = [];
        $this->dispatch('notify', message: match ($action) {
            'publish' => "{$count} {$noun} published.",
            'draft' => "{$count} {$noun} moved to drafts.",
            'delete' => "{$count} {$noun} deleted.",
        });
    }

    public function moveUp(int $id): void
    {
        $pinnedPosts = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $index = $pinnedPosts->search(fn ($p) => $p->id === $id);

        if ($index === false || $index === 0) {
            return;
        }

        $pinnedPosts->each(function (Post $post, int $i) use ($index): void {
            if ($i === $index - 1) {
                $post->sort_order = $index;
            } elseif ($i === $index) {
                $post->sort_order = $index - 1;
            } else {
                $post->sort_order = $i;
            }
            $post->save();
        });
    }

    public function moveDown(int $id): void
    {
        $pinnedPosts = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $index = $pinnedPosts->search(fn ($p) => $p->id === $id);
        $lastIndex = $pinnedPosts->count() - 1;

        if ($index === false || $index === $lastIndex) {
            return;
        }

        $pinnedPosts->each(function (Post $post, int $i) use ($index): void {
            if ($i === $index) {
                $post->sort_order = $index + 1;
            } elseif ($i === $index + 1) {
                $post->sort_order = $index;
            } else {
                $post->sort_order = $i;
            }
            $post->save();
        });
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    #[Computed]
    public function types(): array
    {
        return collect(PostTypeRegistry::all())
            ->mapWithKeys(function (string $type) {
                $handler = PostTypeRegistry::handler($type);

                return [$type => ['label' => $handler->label(), 'icon' => $handler->icon()]];
            })
            ->all();
    }

    private function filteredQuery(): Builder
    {
        return Post::query()
            ->when($this->search !== '', function (Builder $query) {
                $query->where(fn (Builder $q) => $q
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('content', 'like', "%{$this->search}%"));
            })
            ->when($this->type !== '', fn (Builder $query) => $query->where('type', $this->type))
            ->when($this->tag !== '', fn (Builder $query) => $query->whereHas('tags', fn (Builder $tags) => $tags->where('slug', $this->tag)))
            ->when($this->status === 'published', fn (Builder $query) => $query->published())
            ->when($this->status === 'draft', fn (Builder $query) => $query->where('is_draft', true))
            ->when($this->status === 'scheduled', fn (Builder $query) => $query
                ->where('is_draft', false)
                ->where('posted_at', '>', now()))
            ->when($this->status === 'pinned', fn (Builder $query) => $query->where('is_pinned', true));
    }

    public function render(): View
    {
        $pinnedIds = Post::where('is_pinned', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->pluck('id');

        return view('livewire.admin.post-index', [
            'posts' => $this->filteredQuery()->with(['media', 'tags'])->pinnedFirst()->paginate(20),
            'filtering' => $this->search !== '' || $this->type !== '' || $this->status !== '' || $this->tag !== '',
            'tagName' => $this->tag !== '' ? Tag::where('slug', $this->tag)->value('name') : null,
            'firstPinnedId' => $pinnedIds->first(),
            'lastPinnedId' => $pinnedIds->last(),
        ]);
    }
}
