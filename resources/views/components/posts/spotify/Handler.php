<?php

namespace App\PostTypes\Spotify;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Closure;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Spotify Embed';
    }

    public function rules(): array
    {
        return [
            'typeData.spotify_url' => [
                'required',
                'url',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! self::embedUrl((string) $value)) {
                        $fail('Enter a Spotify track, album, playlist, artist, episode or show link.');
                    }
                },
            ],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $url = $typeData['spotify_url'] ?? '';

        return [
            'spotify_url' => self::embedUrl($url) ?? $url,
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

    /**
     * Build the embed URL from share, locale-prefixed (/intl-xx/) or embed links.
     */
    public static function embedUrl(string $url): ?string
    {
        if (parse_url($url, PHP_URL_HOST) !== 'open.spotify.com') {
            return null;
        }

        $pattern = '~^/(?:embed/)?(?:intl-[a-z]{2}(?:-[A-Za-z]{2})?/)?(track|album|playlist|artist|episode|show)/([A-Za-z0-9]+)/?$~';

        if (! preg_match($pattern, parse_url($url, PHP_URL_PATH) ?? '', $matches)) {
            return null;
        }

        return "https://open.spotify.com/embed/{$matches[1]}/{$matches[2]}";
    }
}
