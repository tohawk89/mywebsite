<?php

namespace App\PostTypes\Spotify;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Spotify Embed';
    }

    public function rules(): array
    {
        return [
            'typeData.spotify_url' => ['required', 'url', 'regex:/spotify\.com/'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        return [
            'spotify_url' => $this->toEmbedUrl($typeData['spotify_url'] ?? ''),
        ];
    }

    public function mountData(Post $post): array
    {
        return [
            'spotify_url' => $post->meta_data['spotify_url'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        return [];
    }

    private function toEmbedUrl(string $url): string
    {
        if (str_contains($url, '/embed/')) {
            return $url;
        }

        return str_replace('open.spotify.com/', 'open.spotify.com/embed/', $url);
    }
}
