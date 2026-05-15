<?php

namespace App\PostTypes\Youtube;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'YouTube Video';
    }

    public function rules(): array
    {
        return [
            'typeData.youtube_url' => ['required', 'url', 'regex:/youtube\.com|youtu\.be/'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $url = $typeData['youtube_url'] ?? '';

        return [
            'youtube_url' => $url,
            'embed_url' => $this->toEmbedUrl($url),
        ];
    }

    public function mountData(Post $post): array
    {
        return [
            'youtube_url' => $post->meta_data['youtube_url'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        return [];
    }

    private function toEmbedUrl(string $url): string
    {
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if ($videoId) {
            return "https://www.youtube.com/embed/{$videoId}";
        }

        return $url;
    }
}
