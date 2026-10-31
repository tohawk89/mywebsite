<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\PostTypes\Image\Handler as ImageHandler;
use App\PostTypes\PostTypeRegistry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'published' => Post::published()->count(),
            'drafts' => Post::where('is_draft', true)->count(),
            'scheduled' => Post::where('is_draft', false)->where('posted_at', '>', now())->count(),
            'this_month' => Post::published()->where('posted_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Most recently edited drafts.
     *
     * @return Collection<int, Post>
     */
    #[Computed]
    public function drafts(): Collection
    {
        return Post::where('is_draft', true)->latest('updated_at')->limit(5)->get();
    }

    /**
     * Scheduled posts, soonest first.
     *
     * @return Collection<int, Post>
     */
    #[Computed]
    public function upcoming(): Collection
    {
        return Post::where('is_draft', false)->where('posted_at', '>', now())->orderBy('posted_at')->limit(5)->get();
    }

    /**
     * Published posts per month for the last 12 months, oldest first.
     *
     * @return array<int, array{label: string, month: string, count: int}>
     */
    #[Computed]
    public function postsPerMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        // Grouped in PHP so it works the same on SQLite and MySQL.
        $counts = Post::published()
            ->where('posted_at', '>=', $start)
            ->pluck('posted_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return collect(range(0, 11))
            ->map(function (int $offset) use ($start, $counts) {
                $month = $start->copy()->addMonths($offset);

                return [
                    'label' => $month->format('M'),
                    'month' => $month->format('F Y'),
                    'count' => $counts->get($month->format('Y-m'), 0),
                ];
            })
            ->all();
    }

    /**
     * Post count per type, largest first.
     *
     * @return array<int, array{type: string, label: string, icon: string, count: int}>
     */
    #[Computed]
    public function typeMix(): array
    {
        return Post::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(function (Post $row) {
                $handler = PostTypeRegistry::handler($row->type);

                return ['type' => $row->type, 'label' => $handler->label(), 'icon' => $handler->icon(), 'count' => (int) $row->total];
            })
            ->all();
    }

    /**
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function topTags(): Collection
    {
        return Tag::whereHas('posts')->withCount('posts')->orderByDesc('posts_count')->limit(8)->get();
    }

    /**
     * @return array<int, array{type: string, label: string, icon: string}>
     */
    #[Computed]
    public function quickCreate(): array
    {
        return collect(PostTypeRegistry::all())
            ->reject(fn (string $type) => $type === 'page')
            ->map(fn (string $type) => [
                'type' => $type,
                'label' => PostTypeRegistry::handler($type)->label(),
                'icon' => PostTypeRegistry::handler($type)->icon(),
            ])
            ->values()
            ->all();
    }

    /**
     * Things worth fixing. Each check is either "ok" or a warning with an optional link.
     *
     * @return array<int, array{ok: bool, message: string, url?: string|null}>
     */
    #[Computed]
    public function healthChecks(): array
    {
        $checks = [];

        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);
        $checks[] = request()->isSecure() && $appUrlScheme === 'http'
            ? ['ok' => false, 'message' => 'APP_URL uses http but the site is served over https, so image links trigger browser warnings. Set APP_URL to https in .env.']
            : ['ok' => true, 'message' => 'APP_URL matches how the site is served.'];

        if (in_array(config('media-library.disk_name'), ['public', 'local'], true)) {
            $checks[] = file_exists(public_path('storage'))
                ? ['ok' => true, 'message' => 'Storage link is in place.']
                : ['ok' => false, 'message' => 'Uploaded images won\'t load: run "php artisan storage:link".'];
        }

        if (config('app.debug') && app()->isProduction()) {
            $checks[] = ['ok' => false, 'message' => 'Debug mode is on in production. Set APP_DEBUG=false in .env.'];
        }

        $checks[] = $this->postsCheck(
            Post::where('type', 'repository')->get()->filter(fn (Post $post) => ! array_key_exists('stars', $post->meta_data ?? [])),
            '1 repository post couldn\'t load its details. Open it and save to try again.',
            ':count repository posts couldn\'t load their details. Open them and save to try again.',
            'All repository posts have their details.',
        );

        $checks[] = $this->postsCheck(
            Post::where('type', 'link')->get()->filter(fn (Post $post) => empty($post->meta_data['page_title'] ?? null)),
            '1 link post has no page preview. Open it and save to try again.',
            ':count link posts have no page preview. Open them and save to try again.',
            'All link posts have a page preview.',
        );

        $checks[] = $this->postsCheck(
            Post::where('type', 'image')->with('media')->get()->filter(fn (Post $post) => ImageHandler::images($post)->isEmpty()),
            '1 image post has no photos.',
            ':count image posts have no photos.',
            'All image posts have photos.',
        );

        return $checks;
    }

    /**
     * @param  Collection<int, Post>  $problems
     * @return array{ok: bool, message: string, url?: string|null}
     */
    private function postsCheck(Collection $problems, string $one, string $many, string $fine): array
    {
        if ($problems->isEmpty()) {
            return ['ok' => true, 'message' => $fine];
        }

        $count = $problems->count();

        return [
            'ok' => false,
            'message' => $count === 1 ? $one : str_replace(':count', (string) $count, $many),
            'url' => $count === 1 ? route('posts.edit', $problems->first()) : route('posts.index', ['type' => $problems->first()->type]),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
