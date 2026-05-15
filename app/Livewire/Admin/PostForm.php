<?php

namespace App\Livewire\Admin;

use App\Enums\SnsType;
use App\Models\Post;
use App\Models\Tag;
use App\PostTypes\DefaultHandler;
use App\PostTypes\PostTypeHandler;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
class PostForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public string $type = 'blog';

    public string $title = '';

    public string $content = '';

    public bool $is_pinned = false;

    public bool $is_draft = false;

    public ?string $posted_at = null;

    public string $tags = '';

    /** @var array<string, mixed> */
    public array $typeData = [];

    public $cover_image;

    public $avatar_image;

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->type = $post->type instanceof \BackedEnum ? $post->type->value : (string) $post->type;
            $this->title = $post->title ?? '';
            $this->content = $post->content ?? '';
            $this->is_pinned = (bool) $post->is_pinned;
            $this->is_draft = (bool) $post->is_draft;
            $this->posted_at = $post->posted_at?->format('Y-m-d\TH:i');
            $this->tags = $post->tags->pluck('name')->implode(', ');
            $this->typeData = $this->resolveHandler()->mountData($post);
        } else {
            $this->posted_at = now()->format('Y-m-d\TH:i');
        }
    }

    public function rules(): array
    {
        $base = [
            'type' => ['required', 'string', Rule::in($this->availableTypes())],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_pinned' => ['boolean'],
            'posted_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'avatar_image' => ['nullable', 'image', 'max:5120'],
        ];

        return array_merge($base, $this->resolveHandler()->rules());
    }

    public function updatedType(): void
    {
        $this->typeData = [];
    }

    public function addSns(): void
    {
        $this->typeData['sns'][] = ['platform' => SnsType::GitHub->value, 'url' => ''];
    }

    public function removeSns(int $index): void
    {
        array_splice($this->typeData['sns'], $index, 1);
        $this->typeData['sns'] = array_values($this->typeData['sns']);
    }

    public function saveDraft(): void
    {
        $this->persistPost(isDraft: true);
    }

    public function save(): void
    {
        $this->persistPost(isDraft: false);
    }

    private function persistPost(bool $isDraft): void
    {
        $this->validate();

        $handler = $this->resolveHandler();
        $metaData = $handler->buildMetaData($this->typeData);

        $wasNotPinned = ! ($this->post?->is_pinned ?? false);

        if ($this->type === 'repository' && empty($this->title)) {
            $this->title = $metaData['repo_name'] ?? $this->typeData['repo_url'] ?? '';
        }

        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'is_pinned' => $this->is_pinned,
            'is_draft' => $isDraft,
            'posted_at' => $this->posted_at,
            'meta_data' => $metaData,
        ];

        if ($this->post) {
            $this->post->update($data);
        } else {
            $this->post = Post::create($data);
        }

        if ($this->is_pinned && $wasNotPinned) {
            $maxOrder = Post::where('is_pinned', true)
                ->where('id', '!=', $this->post->id)
                ->max('sort_order') ?? -1;
            $this->post->update(['sort_order' => $maxOrder + 1]);
        }

        if ($this->tags) {
            $tagNames = array_map('trim', explode(',', $this->tags));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if (empty($name)) {
                    continue;
                }
                $tag = Tag::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
                $tagIds[] = $tag->id;
            }
            $this->post->tags()->sync($tagIds);
        } else {
            $this->post->tags()->detach();
        }

        foreach ($handler->mediaCollections() as $collection) {
            $property = $collection === 'cover' ? 'cover_image' : "{$collection}_image";
            if ($this->$property) {
                $this->post->clearMediaCollection($collection);
                $this->post->addMediaFromDisk(
                    FileUploadConfiguration::path($this->$property->getFilename(), false),
                    FileUploadConfiguration::disk()
                )
                    ->usingFileName($this->$property->getClientOriginalName())
                    ->toMediaCollection($collection);
            }
        }

        $statusMessage = $isDraft ? 'Post saved as draft.' : 'Post published successfully.';
        session()->flash('status', $statusMessage);

        $this->redirect(route('posts.index'), navigate: true);
    }

    private function resolveHandler(): PostTypeHandler
    {
        $class = 'App\\PostTypes\\'.ucfirst($this->type).'\\Handler';

        return class_exists($class) ? new $class : new DefaultHandler($this->type);
    }

    #[Computed]
    public function availableTypes(): array
    {
        return collect(File::directories(resource_path('views/components/posts')))
            ->map(fn ($dir) => basename($dir))
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function typesWithLabels(): array
    {
        return collect($this->availableTypes())
            ->map(function (string $type) {
                $class = 'App\\PostTypes\\'.ucfirst($type).'Handler';
                $handler = class_exists($class) ? new $class : new DefaultHandler($type);

                return ['value' => $type, 'label' => $handler->label()];
            })
            ->all();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.post-form', [
            'handler' => $this->resolveHandler(),
        ]);
    }
}
