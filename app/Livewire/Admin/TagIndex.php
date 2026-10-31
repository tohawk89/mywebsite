<?php

namespace App\Livewire\Admin;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * All tags with their post counts: rename (merging into an existing tag with the same name), delete,
 * and jump to the tag's posts.
 */
#[Layout('layouts::admin')]
#[Title('Tags')]
class TagIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $this->editingName = Tag::findOrFail($id)->name;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingName');
    }

    /**
     * Rename the tag. If another tag already has that name, move this tag's posts to it and delete this one.
     */
    public function rename(): void
    {
        $this->validate(['editingName' => ['required', 'string', 'max:50']], [], ['editingName' => 'name']);

        $tag = Tag::findOrFail($this->editingId);
        $name = trim($this->editingName);
        $slug = Tag::slugFor($name);
        $existing = Tag::where('slug', $slug)->whereKeyNot($tag->id)->first();

        if ($existing) {
            DB::transaction(function () use ($tag, $existing) {
                $existing->posts()->syncWithoutDetaching($tag->posts()->pluck('posts.id'));
                $tag->posts()->detach();
                $tag->delete();
            });
            $this->dispatch('notify', message: "Merged into {$existing->name}.");
        } else {
            $tag->update(['name' => $name, 'slug' => $slug]);
            $this->dispatch('notify', message: 'Tag renamed.');
        }

        $this->cancelEdit();
    }

    public function delete(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $tag->posts()->detach();
        $tag->delete();
        $this->dispatch('notify', message: "Deleted {$tag->name}.");
    }

    public function deleteUnused(): void
    {
        $count = Tag::doesntHave('posts')->delete();
        $this->dispatch('notify', message: "Deleted {$count} unused ".str('tag')->plural($count).'.');
    }

    public function render(): View
    {
        return view('livewire.admin.tag-index', [
            'tags' => Tag::query()
                ->withCount('posts')
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->paginate(30),
            'unusedCount' => Tag::doesntHave('posts')->count(),
        ]);
    }
}
