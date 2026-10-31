<?php

namespace App\PostTypes\Youtube;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Closure;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'YouTube Video';
    }

    public function icon(): string
    {
        return 'bi-youtube';
    }

    public function rules(): array
    {
        return [
            'typeData.youtube_url' => [
                'required',
                'url',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! self::videoId((string) $value)) {
                        $fail('Enter a valid YouTube video link.');
                    }
                },
            ],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $url = $typeData['youtube_url'] ?? '';
        $videoId = self::videoId($url);

        return [
            'youtube_url' => $url,
            'video_id' => $videoId,
            'embed_url' => $videoId ? "https://www.youtube.com/embed/{$videoId}" : $url,
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

    /**
     * Extract the 11-character video ID from watch, youtu.be, shorts, live and embed links.
     */
    public static function videoId(string $url): ?string
    {
        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $url, $matches) ? $matches[1] : null;
    }
}
