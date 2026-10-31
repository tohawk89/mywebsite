<?php

namespace App\PostTypes\Link;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Closure;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shares a website: on save it reads the page's title, description, preview image and icon
 * (Open Graph / Twitter tags, falling back to <title> and the meta description).
 */
class Handler implements PostTypeHandler
{
    /** Only read the start of the page; the tags we need live in <head>. */
    private const MAX_BYTES = 512 * 1024;

    /**
     * Resolves a host to IP addresses. Swappable in tests so they don't need DNS.
     *
     * @var (Closure(string): array<int, string>)|null
     */
    public static ?Closure $resolveHostUsing = null;

    public function label(): string
    {
        return 'Website link';
    }

    public function icon(): string
    {
        return 'bi-link-45deg';
    }

    public function rules(): array
    {
        return [
            'typeData.url' => ['required', 'url:http,https', 'max:2048'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        $url = trim((string) ($typeData['url'] ?? ''));

        return array_merge(
            ['url' => $url, 'domain' => self::domain($url)],
            $this->fetchPreview($url),
            ['fetched_at' => now()->toIso8601String()],
        );
    }

    public function mountData(Post $post): array
    {
        return [
            'url' => $post->meta_data['url'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        return [];
    }

    public static function domain(string $url): string
    {
        return Str::after((string) parse_url($url, PHP_URL_HOST), 'www.');
    }

    /**
     * @return array<string, string|null>
     */
    private function fetchPreview(string $url): array
    {
        if (! self::isPublicUrl($url)) {
            return [];
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; '.config('app.name').' link preview)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->withOptions(['allow_redirects' => [
                    'max' => 3,
                    'protocols' => ['http', 'https'],
                    // Re-check every redirect so a public URL can't bounce to an internal address.
                    'on_redirect' => function ($request, $response, $uri) {
                        if (! self::isPublicUrl((string) $uri)) {
                            throw new \RuntimeException('Redirect to a non-public address.');
                        }
                    },
                ]])
                ->get($url);
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful() || ! str_contains(strtolower($response->header('Content-Type')), 'html')) {
            return [];
        }

        return $this->parse(substr($response->body(), 0, self::MAX_BYTES), $url);
    }

    /**
     * @return array<string, string|null>
     */
    private function parse(string $html, string $url): array
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR);
        libxml_clear_errors();
        $xpath = new DOMXPath($document);

        $meta = function (string ...$names) use ($xpath): ?string {
            foreach ($names as $name) {
                $node = $xpath->query("//meta[@property='{$name}' or @name='{$name}']/@content")->item(0);
                if ($node && trim($node->nodeValue) !== '') {
                    return trim($node->nodeValue);
                }
            }

            return null;
        };

        $title = $meta('og:title', 'twitter:title') ?? trim((string) $xpath->query('//title')->item(0)?->textContent);
        $icon = $xpath->query("//link[contains(concat(' ', translate(@rel, 'ICON', 'icon'), ' '), ' icon ')]/@href")->item(0)?->nodeValue;

        return [
            'page_title' => Str::limit($title ?: '', 200, '…') ?: null,
            'description' => Str::limit($meta('og:description', 'twitter:description', 'description') ?? '', 300, '…') ?: null,
            'image' => self::absolute($meta('og:image', 'og:image:url', 'twitter:image'), $url),
            'site_name' => Str::limit($meta('og:site_name', 'application-name') ?? '', 80, '…') ?: null,
            'favicon' => self::absolute($icon ?: '/favicon.ico', $url),
        ];
    }

    /**
     * Resolve a possibly relative URL against the page URL; only http(s) results are kept.
     */
    private static function absolute(?string $href, string $base): ?string
    {
        $href = trim((string) $href);

        if ($href === '') {
            return null;
        }

        if (str_starts_with($href, '//')) {
            $href = parse_url($base, PHP_URL_SCHEME).':'.$href;
        } elseif (! preg_match('#^[a-z][a-z0-9+.-]*:#i', $href)) {
            $origin = parse_url($base, PHP_URL_SCHEME).'://'.parse_url($base, PHP_URL_HOST)
                .(parse_url($base, PHP_URL_PORT) ? ':'.parse_url($base, PHP_URL_PORT) : '');
            $path = str_starts_with($href, '/')
                ? $href
                : rtrim(Str::beforeLast((string) parse_url($base, PHP_URL_PATH), '/'), '/').'/'.$href;
            $href = $origin.$path;
        }

        return in_array(parse_url($href, PHP_URL_SCHEME), ['http', 'https'], true) ? $href : null;
    }

    /**
     * True when the URL is http(s) and its host resolves only to public IP addresses,
     * so the server can't be pointed at localhost or the internal network.
     */
    public static function isPublicUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) || ! $host) {
            return false;
        }

        $host = trim($host, '[]');
        $ips = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : (self::$resolveHostUsing ?? fn (string $name) => gethostbynamel($name) ?: [])($host);

        if ($ips === []) {
            return false;
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }
}
