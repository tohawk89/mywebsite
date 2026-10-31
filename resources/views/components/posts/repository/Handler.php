<?php

namespace App\PostTypes\Repository;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use App\PostTypes\ProvidesDefaultTitle;
use App\Support\SiteOptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Handler implements PostTypeHandler, ProvidesDefaultTitle
{
    public function label(): string
    {
        return 'Git Repository';
    }

    public function icon(): string
    {
        return 'bi-git';
    }

    public function rules(): array
    {
        return [
            'typeData.repo_url' => ['required', 'url', 'regex:/github\.com|gitlab\.com|bitbucket\.org/'],
            'typeData.repo_title' => ['nullable', 'string', 'max:255'],
            'typeData.repo_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $repoData = $this->fetchRepoData($typeData['repo_url'] ?? '');

        return array_merge($repoData, [
            'repo_url' => $typeData['repo_url'] ?? '',
            'title' => ($typeData['repo_title'] ?? '') ?: null,
            'note' => ($typeData['repo_note'] ?? '') ?: null,
            'fetched_at' => now()->toIso8601String(),
        ]);
    }

    public function mountData(Post $post): array
    {
        return [
            'repo_url' => $post->meta_data['repo_url'] ?? '',
            'repo_title' => $post->meta_data['title'] ?? '',
            'repo_note' => $post->meta_data['note'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        return [];
    }

    public function defaultTitle(array $metaData, array $typeData): string
    {
        return $metaData['repo_name'] ?? $typeData['repo_url'] ?? '';
    }

    private function fetchRepoData(string $url): array
    {
        $host = strtolower(preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST)));
        $path = $this->repoPath($host, $url);

        if (substr_count($path, '/') < 1) {
            return ['repo_name' => $path ?: $url];
        }

        try {
            $data = match ($host) {
                'github.com' => $this->fetchGithub($path),
                'gitlab.com' => $this->fetchGitlab($path),
                'bitbucket.org' => $this->fetchBitbucket($path),
                default => null,
            };
        } catch (ConnectionException) {
            $data = null;
        }

        return $data ?? ['repo_name' => $path];
    }

    /**
     * Normalize the repository path: GitLab allows nested groups (group/subgroup/repo),
     * GitHub and Bitbucket are always owner/repo. Drops ".git" and file/branch suffixes.
     */
    private function repoPath(string $host, string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($host === 'gitlab.com') {
            $path = Str::before($path, '/-/');
        } else {
            $path = implode('/', array_slice(explode('/', $path), 0, 2));
        }

        return preg_replace('/\.git$/', '', $path);
    }

    private function client(): PendingRequest
    {
        return Http::timeout(5)->withHeaders(['User-Agent' => config('app.name', 'Laravel')]);
    }

    private function fetchGithub(string $path): ?array
    {
        $client = $this->client()->withHeaders(['Accept' => 'application/vnd.github.v3+json']);

        // A token from Site settings > Integrations raises GitHub's limit from about 60 to 5,000 lookups an hour.
        if ($token = SiteOptions::githubToken()) {
            $client = $client->withToken($token);
        }

        $response = $client->get("https://api.github.com/repos/{$path}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
            'repo_name' => $data['full_name'] ?? $path,
            'repo_description' => $data['description'] ?? null,
            'language' => $data['language'] ?? null,
            'stars' => $data['stargazers_count'] ?? 0,
            'forks' => $data['forks_count'] ?? 0,
            'topics' => $data['topics'] ?? [],
            'owner_avatar' => $data['owner']['avatar_url'] ?? null,
        ];
    }

    private function fetchGitlab(string $path): ?array
    {
        $api = 'https://gitlab.com/api/v4/projects/'.rawurlencode($path);
        $response = $this->client()->get($api);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        // Languages come from a separate endpoint as {"Go": 98.4, ...}; keep the largest.
        $languages = $this->client()->get("{$api}/languages")->json();
        $language = null;
        if (is_array($languages) && $languages !== []) {
            arsort($languages);
            $language = array_key_first($languages);
        }

        $avatar = $data['avatar_url'] ?? $data['namespace']['avatar_url'] ?? null;
        if ($avatar && str_starts_with($avatar, '/')) {
            $avatar = "https://gitlab.com{$avatar}";
        }

        return [
            'repo_name' => $data['path_with_namespace'] ?? $path,
            'repo_description' => $data['description'] ?? null,
            'language' => $language,
            'stars' => $data['star_count'] ?? 0,
            'forks' => $data['forks_count'] ?? 0,
            'topics' => $data['topics'] ?? [],
            'owner_avatar' => $avatar,
        ];
    }

    private function fetchBitbucket(string $path): ?array
    {
        $api = "https://api.bitbucket.org/2.0/repositories/{$path}";
        $response = $this->client()->get($api);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        // Bitbucket has no star count; watchers are the closest equivalent. Counts are paginated totals.
        $count = fn (string $endpoint): int => (int) ($this->client()->get("{$api}/{$endpoint}", ['pagelen' => 1])->json('size') ?? 0);

        return [
            'repo_name' => $data['full_name'] ?? $path,
            'repo_description' => ($data['description'] ?? '') ?: null,
            'language' => ($data['language'] ?? '') !== '' ? Str::ucfirst($data['language']) : null,
            'stars' => $count('watchers'),
            'forks' => $count('forks'),
            'topics' => [],
            'owner_avatar' => $data['owner']['links']['avatar']['href'] ?? $data['links']['avatar']['href'] ?? null,
        ];
    }
}
