<?php

namespace App\PostTypes\Repository;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Illuminate\Support\Facades\Http;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Git Repository';
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
}
