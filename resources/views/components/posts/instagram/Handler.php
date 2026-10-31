<?php

namespace App\PostTypes\Instagram;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Illuminate\Support\Str;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Instagram Post';
    }

    public function rules(): array
    {
        return [
            'typeData.instagram_url' => ['required', 'url', 'regex:/instagram\.com\/(p|reel)\//'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $url = $typeData['instagram_url'] ?? '';

        return [
            'instagram_url' => $url,
            'instagram_embed_url' => $this->toEmbedUrl($url),
        ];
    }

    public function mountData(Post $post): array
    {
        return [
            'instagram_url' => $post->meta_data['instagram_url'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        return [];
    }

    /**
     * The captioned embed shows the post's Instagram caption under the media.
     * Accepts a post URL or an older plain "/embed" URL.
     */
    public static function captionedEmbedUrl(string $url): string
    {
        return rtrim(Str::before($url, '/embed'), '/').'/embed/captioned/';
    }

    private function toEmbedUrl(string $url): string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segments = array_values(array_filter(explode('/', $path)));

        if (count($segments) >= 2 && in_array($segments[0], ['p', 'reel'], true)) {
            return self::captionedEmbedUrl("https://www.instagram.com/{$segments[0]}/{$segments[1]}");
        }

        return $url;
    }
}
