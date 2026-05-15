<?php

namespace App\Livewire\Admin;

use App\Enums\PostType;
use App\Enums\SnsType;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
class PostForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    // Form Fields
    public $type = 'blog';

    public $title = '';

    public $content = '';

    public $is_pinned = false;

    public $is_draft = false;

    public $posted_at;

    public $tags = ''; // Comma separated tags

    // Dynamic Fields (mapped to meta_data or specific logic)
    public $spotify_url = '';

    public $youtube_url = '';

    public $instagram_url = '';

    // Repository Fields
    public $repo_url = '';

    public $repo_title = '';

    public $repo_note = '';

    // Profile Fields
    /** @var array<int, array{platform: string, url: string}> */
    public array $sns = [];

    // Media
    public $cover_image;

    public $avatar_image;

    public function mount(?Post $post = null)
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->type = $post->type->value;
            $this->title = $post->title;
            $this->content = $post->content;
            $this->is_pinned = $post->is_pinned;
            $this->is_draft = $post->is_draft;
            $this->posted_at = $post->posted_at?->format('Y-m-d\TH:i');

            // Tags
            $this->tags = $post->tags->pluck('name')->implode(', ');

            // Meta Data
            $this->spotify_url = $post->meta_data['spotify_url'] ?? '';
            $this->youtube_url = $post->meta_data['youtube_url'] ?? '';
            $this->instagram_url = $post->meta_data['instagram_url'] ?? '';
            $this->repo_url = $post->meta_data['repo_url'] ?? '';
            $this->repo_title = $post->meta_data['title'] ?? '';
            $this->repo_note = $post->meta_data['note'] ?? '';
            $this->sns = $post->meta_data['sns'] ?? [];
        } else {
            $this->type = PostType::BLOG->value;
            $this->posted_at = now()->format('Y-m-d\TH:i');
        }
    }

    public function rules()
    {
        $rules = [
            'type' => ['required', Rule::enum(PostType::class)],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_pinned' => ['boolean'],
            'posted_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'], // 5MB
            'avatar_image' => ['nullable', 'image', 'max:5120'], // 5MB
            'sns' => ['nullable', 'array'],
            'sns.*.platform' => ['required', Rule::enum(SnsType::class)],
            'sns.*.url' => ['required', 'url', 'max:500'],
        ];

        // Dynamic Validation
        if ($this->type === PostType::SPOTIFY->value) {
            $rules['spotify_url'] = ['required', 'url', 'regex:/spotify\.com/'];
        }

        if ($this->type === PostType::YOUTUBE->value) {
            $rules['youtube_url'] = ['required', 'url', 'regex:/youtube\.com|youtu\.be/'];
        }

        if ($this->type === PostType::INSTAGRAM->value) {
            $rules['instagram_url'] = ['required', 'url', 'regex:/instagram\.com\/(p|reel)\//'];
        }

        if ($this->type === PostType::REPOSITORY->value) {
            $rules['repo_url'] = ['required', 'url', 'regex:/github\.com|gitlab\.com|bitbucket\.org/'];
            $rules['repo_title'] = ['nullable', 'string', 'max:255'];
            $rules['repo_note'] = ['nullable', 'string', 'max:500'];
        }

        if ($this->type === PostType::QUOTE->value) {
            $rules['content'] = ['required', 'string']; // Quote text
            $rules['title'] = ['required', 'string'];   // Author
        }

        if ($this->type === PostType::BLOG->value) {
            $rules['title'] = ['required', 'string', 'max:255'];
            // Content is optional but recommended
        }

        if ($this->type === PostType::PROFILE->value) {
            $rules['title'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function addSns(): void
    {
        $this->sns[] = ['platform' => SnsType::GitHub->value, 'url' => ''];
    }

    public function removeSns(int $index): void
    {
        array_splice($this->sns, $index, 1);
        $this->sns = array_values($this->sns);
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

        $metaData = [];
        if ($this->type === PostType::SPOTIFY->value) {
            $metaData['spotify_url'] = $this->getEmbedUrl($this->spotify_url, 'spotify');
        } elseif ($this->type === PostType::YOUTUBE->value) {
            $metaData['youtube_url'] = $this->youtube_url;
            $metaData['embed_url'] = $this->getEmbedUrl($this->youtube_url, 'youtube');
        } elseif ($this->type === PostType::INSTAGRAM->value) {
            $metaData['instagram_url'] = $this->instagram_url;
            $metaData['instagram_embed_url'] = $this->getEmbedUrl($this->instagram_url, 'instagram');
        } elseif ($this->type === PostType::REPOSITORY->value) {
            $repoData = $this->fetchRepoData($this->repo_url);
            $metaData = array_merge($repoData, [
                'repo_url' => $this->repo_url,
                'title' => $this->repo_title ?: null,
                'note' => $this->repo_note ?: null,
                'fetched_at' => now()->toIso8601String(),
            ]);
        } elseif ($this->type === PostType::PROFILE->value) {
            $metaData['sns'] = $this->sns;
        }

        $wasNotPinned = ! ($this->post?->is_pinned ?? false);

        // Auto-populate post title from repo name for repository posts
        if ($this->type === PostType::REPOSITORY->value && empty($this->title)) {
            $this->title = $metaData['repo_name'] ?? $this->repo_url;
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

        // Assign sort_order when a post is newly pinned (goes to end of pinned list)
        if ($this->is_pinned && $wasNotPinned) {
            $maxOrder = Post::where('is_pinned', true)
                ->where('id', '!=', $this->post->id)
                ->max('sort_order') ?? -1;
            $this->post->update(['sort_order' => $maxOrder + 1]);
        }

        // Handle Tags
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

        // Handle Media
        if ($this->cover_image) {
            $this->post->clearMediaCollection('cover');
            $this->post->addMediaFromDisk(
                FileUploadConfiguration::path($this->cover_image->getFilename(), false),
                FileUploadConfiguration::disk()
            )
                ->usingFileName($this->cover_image->getClientOriginalName())
                ->toMediaCollection('cover');
        }

        if ($this->avatar_image) {
            $this->post->clearMediaCollection('avatar');
            $this->post->addMediaFromDisk(
                FileUploadConfiguration::path($this->avatar_image->getFilename(), false),
                FileUploadConfiguration::disk()
            )
                ->usingFileName($this->avatar_image->getClientOriginalName())
                ->toMediaCollection('avatar');
        }

        $statusMessage = $isDraft ? 'Post saved as draft.' : 'Post published successfully.';
        session()->flash('status', $statusMessage);

        $this->redirect(route('posts.index'), navigate: true);
    }

    private function fetchRepoData(string $url): array
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segments = array_values(array_filter(explode('/', $path)));

        if (count($segments) < 2) {
            return ['repo_name' => $path ?: $url];
        }

        $owner = $segments[0];
        $repo = $segments[1];

        if (! str_contains($url, 'github.com')) {
            return ['repo_name' => "{$owner}/{$repo}"];
        }

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => config('app.name', 'Laravel'),
        ])->get("https://api.github.com/repos/{$owner}/{$repo}");

        if (! $response->successful()) {
            return ['repo_name' => "{$owner}/{$repo}"];
        }

        $data = $response->json();

        return [
            'repo_name' => $data['full_name'] ?? "{$owner}/{$repo}",
            'repo_description' => $data['description'] ?? null,
            'language' => $data['language'] ?? null,
            'stars' => $data['stargazers_count'] ?? 0,
            'forks' => $data['forks_count'] ?? 0,
            'topics' => $data['topics'] ?? [],
            'owner_avatar' => $data['owner']['avatar_url'] ?? null,
        ];
    }

    private function getEmbedUrl($url, $type)
    {
        if ($type === 'spotify') {
            // Converts https://open.spotify.com/track/xyz -> https://open.spotify.com/embed/track/xyz
            if (str_contains($url, '/embed/')) {
                return $url;
            }

            return str_replace('open.spotify.com/', 'open.spotify.com/embed/', $url);
        }

        if ($type === 'youtube') {
            // Handles youtu.be/xyz and youtube.com/watch?v=xyz
            $videoId = '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                $videoId = $matches[1];
            }

            if ($videoId) {
                return "https://www.youtube.com/embed/{$videoId}";
            }

            return $url; // Fallback
        }

        if ($type === 'instagram') {
            $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
            $segments = array_values(array_filter(explode('/', $path)));

            if (count($segments) >= 2 && in_array($segments[0], ['p', 'reel'], true)) {
                return "https://www.instagram.com/{$segments[0]}/{$segments[1]}/embed";
            }

            return $url;
        }

        return $url;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.post-form', [
            'types' => PostType::cases(),
            'snsTypes' => SnsType::cases(),
        ]);
    }
}
