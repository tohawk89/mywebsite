<?php

namespace App\Livewire\Admin;

use App\Ai\Agents\WritingAssistant;
use App\Enums\SnsType;
use App\Models\Post;
use App\Models\Tag;
use App\PostTypes\HandlesMedia;
use App\PostTypes\PostTypeHandler;
use App\PostTypes\PostTypeRegistry;
use App\PostTypes\ProvidesDefaultTitle;
use App\Support\AiWriting;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
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

    /** Hide the title on the public site (it still shows in the admin). */
    public bool $hide_title = false;

    public bool $hide_date = false;

    public ?string $posted_at = null;

    public string $tags = '';

    /** @var array<string, mixed> */
    public array $typeData = [];

    public $cover_image;

    public $avatar_image;

    /** Fields the AI writing menu works on. */
    public const AI_FIELDS = ['title', 'content'];

    /**
     * A pending AI rewrite awaiting Accept, Try again or Discard.
     *
     * @var array{field: string, action: string, original: string, start: int, length: int, result: string}|null
     */
    public ?array $aiSuggestion = null;

    /** @var array<int, string> Title ideas from AI. */
    public array $aiTitles = [];

    /** @var array<int, string> Tag ideas from AI that aren't on the post yet. */
    public array $aiTags = [];

    public ?string $aiError = null;

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->type = $post->type instanceof \BackedEnum ? $post->type->value : (string) $post->type;
            $this->title = $post->title ?? '';
            $this->content = $post->content ?? '';
            $this->is_pinned = (bool) $post->is_pinned;
            $this->is_draft = (bool) $post->is_draft;
            $this->hide_title = (bool) $post->hide_title;
            $this->hide_date = (bool) $post->hide_date;
            $this->posted_at = $post->posted_at?->format('Y-m-d\TH:i');
            $this->tags = $post->tags->pluck('name')->implode(', ');
            $this->typeData = $this->resolveHandler()->mountData($post);
        } else {
            $this->posted_at = now()->format('Y-m-d\TH:i');

            // Quick create links (e.g. from the dashboard) preselect a type with ?type=...
            $requested = (string) request()->query('type', '');
            if (in_array($requested, $this->availableTypes(), true)) {
                $this->type = $requested;
            }
        }
    }

    public function rules(): array
    {
        $base = [
            'type' => ['required', 'string', Rule::in($this->availableTypes())],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_pinned' => ['boolean'],
            'hide_title' => ['boolean'],
            'hide_date' => ['boolean'],
            'posted_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'avatar_image' => ['nullable', 'image', 'max:5120'],
        ];

        return array_merge($base, $this->resolveHandler()->rules());
    }

    /**
     * Rewrite a field (or the selected part of it) with an AI writing action.
     */
    public function aiRewrite(string $field, string $action, int $start = 0, string $selected = ''): void
    {
        if (! in_array($field, self::AI_FIELDS, true) || ! array_key_exists($action, WritingAssistant::ACTIONS)) {
            return;
        }

        $text = (string) $this->{$field};
        [$start, $length] = $this->locateSelection($text, $start, $selected);
        $original = $length > 0 ? mb_substr($text, $start, $length) : $text;

        if (trim($original) === '') {
            $this->aiError = 'Write something first, then ask AI to work on it.';

            return;
        }

        $this->runAi(function () use ($field, $action, $original, $start, $length) {
            $this->aiSuggestion = [
                'field' => $field,
                'action' => $action,
                'original' => $original,
                'start' => $length > 0 ? $start : 0,
                'length' => $length > 0 ? $length : mb_strlen((string) $this->{$field}),
                'result' => AiWriting::rewrite($action, $original),
            ];
        });
    }

    public function retryAi(): void
    {
        if ($suggestion = $this->aiSuggestion) {
            $this->runAi(fn () => $this->aiSuggestion['result'] = AiWriting::rewrite($suggestion['action'], $suggestion['original']));
        }
    }

    public function acceptAi(): void
    {
        if (! $suggestion = $this->aiSuggestion) {
            return;
        }

        $text = (string) $this->{$suggestion['field']};
        $this->{$suggestion['field']} = mb_substr($text, 0, $suggestion['start'])
            .$suggestion['result']
            .mb_substr($text, $suggestion['start'] + $suggestion['length']);

        // Rich text editors keep their own copy of the text and listen for this.
        $this->dispatch('ai-applied', field: $suggestion['field'], value: $this->{$suggestion['field']});
        $this->aiSuggestion = null;
    }

    public function discardAi(): void
    {
        $this->aiSuggestion = null;
        $this->aiTitles = [];
        $this->aiError = null;
    }

    public function suggestTitles(): void
    {
        $source = trim($this->content) !== '' ? $this->content : $this->title;

        if (trim($source) === '') {
            $this->aiError = 'Write some content first, then ask for title ideas.';

            return;
        }

        $this->runAi(fn () => $this->aiTitles = AiWriting::suggestTitles($source));
    }

    public function useTitle(int $index): void
    {
        if (isset($this->aiTitles[$index])) {
            $this->title = $this->aiTitles[$index];
            $this->aiTitles = [];
        }
    }

    public function suggestTags(): void
    {
        if (trim($this->title.$this->content) === '') {
            $this->aiError = 'Add a title or content first, then ask for tag ideas.';

            return;
        }

        $this->runAi(function () {
            $current = array_map('mb_strtolower', $this->tagNames());
            $this->aiTags = array_values(array_filter(
                AiWriting::suggestTags($this->title, $this->content, Tag::orderBy('name')->pluck('name')->all()),
                fn (string $tag) => ! in_array(mb_strtolower($tag), $current, true),
            ));
        });
    }

    public function addSuggestedTag(string $tag): void
    {
        $this->tags = implode(', ', array_unique([...$this->tagNames(), trim($tag)]));
        $this->aiTags = array_values(array_diff($this->aiTags, [$tag]));
    }

    /**
     * @return array<int, string>
     */
    private function tagNames(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    /**
     * Find the selected text in the field. Browsers count positions in UTF-16 units, so the
     * selected text itself is used to locate it, starting near the reported position.
     *
     * @return array{0: int, 1: int} Start and length; length 0 means the whole field.
     */
    private function locateSelection(string $text, int $start, string $selected): array
    {
        if ($selected === '') {
            return [0, 0];
        }

        $position = mb_strpos($text, $selected, max(0, min($start, mb_strlen($text)) - 50));
        $position = $position === false ? mb_strpos($text, $selected) : $position;

        return $position === false ? [0, 0] : [$position, mb_strlen($selected)];
    }

    /**
     * Run an AI request with a per-admin rate limit and friendly errors.
     */
    private function runAi(callable $request): void
    {
        $this->aiError = null;
        $key = 'ai-writing:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->aiError = 'That\'s a lot of AI requests. Wait '.RateLimiter::availableIn($key).' seconds and try again.';

            return;
        }

        RateLimiter::hit($key, 60);

        try {
            $request();
        } catch (\Throwable $exception) {
            report($exception);
            $this->aiError = AiWriting::errorMessage($exception);
        }
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

        if (empty($this->title) && $handler instanceof ProvidesDefaultTitle) {
            $this->title = $handler->defaultTitle($metaData, $this->typeData);
        }

        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'is_pinned' => $this->is_pinned,
            'is_draft' => $isDraft,
            'hide_title' => $this->hide_title,
            'hide_date' => $this->hide_date,
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
                $tag = Tag::firstOrCreate(['slug' => Tag::slugFor($name)], ['name' => $name]);
                $tagIds[] = $tag->id;
            }
            $this->post->tags()->sync(array_unique($tagIds));
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

        if ($handler instanceof HandlesMedia) {
            $handler->saveMedia($this->post, $this->typeData);
        }

        $statusMessage = $isDraft ? 'Post saved as draft.' : 'Post published successfully.';
        session()->flash('status', $statusMessage);

        $this->redirect(route('posts.index'), navigate: true);
    }

    private function resolveHandler(): PostTypeHandler
    {
        return $this->handlerFor($this->type);
    }

    private function handlerFor(string $type): PostTypeHandler
    {
        return PostTypeRegistry::handler($type);
    }

    /**
     * @return array<int, string> Existing tag names for the tag picker.
     */
    #[Computed]
    public function allTagNames(): array
    {
        return Tag::orderBy('name')->pluck('name')->all();
    }

    #[Computed]
    public function availableTypes(): array
    {
        return PostTypeRegistry::all();
    }

    #[Computed]
    public function typesWithLabels(): array
    {
        return collect($this->availableTypes())
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => $this->handlerFor($type)->label(),
                'icon' => $this->handlerFor($type)->icon(),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin.post-form', [
            'handler' => $this->resolveHandler(),
        ])->title($this->post ? 'Edit post' : 'New post');
    }
}
