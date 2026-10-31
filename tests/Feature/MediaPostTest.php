<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use App\PostTypes\Spotify\Handler as SpotifyHandler;
use App\PostTypes\Youtube\Handler as YoutubeHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MediaPostTest extends TestCase
{
    use RefreshDatabase;

    public static function youtubeUrls(): array
    {
        return [
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'watch with extra params' => ['https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=42'],
            'short link' => ['https://youtu.be/dQw4w9WgXcQ?si=abc'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ'],
            'live' => ['https://www.youtube.com/live/dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube.com/embed/dQw4w9WgXcQ'],
        ];
    }

    #[DataProvider('youtubeUrls')]
    public function test_youtube_video_id_is_extracted(string $url): void
    {
        $this->assertSame('dQw4w9WgXcQ', YoutubeHandler::videoId($url));
    }

    public function test_youtube_post_rejects_links_without_a_video(): void
    {
        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class)
            ->set('type', 'youtube')
            ->set('typeData', ['youtube_url' => 'https://www.youtube.com/@laravelphp'])
            ->call('save')
            ->assertHasErrors(['typeData.youtube_url']);
    }

    public function test_youtube_card_shows_thumbnail_instead_of_player(): void
    {
        Post::factory()->create([
            'type' => 'youtube',
            'title' => 'Video',
            'meta_data' => ['youtube_url' => 'https://youtu.be/dQw4w9WgXcQ'],
        ]);

        $this->get('/')
            ->assertSee('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false)
            ->assertSeeInOrder([
                '<template x-if="playing">',
                '<iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1"',
            ], false);
    }

    public static function spotifyEmbeds(): array
    {
        return [
            'track uses compact player' => ['https://open.spotify.com/embed/track/abc', '152'],
            'episode uses compact player' => ['https://open.spotify.com/embed/episode/abc', '152'],
            'playlist uses tall player' => ['https://open.spotify.com/embed/playlist/abc', '352'],
            'album uses tall player' => ['https://open.spotify.com/embed/album/abc', '352'],
        ];
    }

    #[DataProvider('spotifyEmbeds')]
    public function test_spotify_card_height_matches_content_type(string $embedUrl, string $height): void
    {
        Post::factory()->create(['type' => 'spotify', 'meta_data' => ['spotify_url' => $embedUrl]]);

        $this->get('/')->assertSeeInOrder([$embedUrl, 'height="'.$height.'"'], false);
    }

    public static function spotifyUrls(): array
    {
        return [
            'share link' => ['https://open.spotify.com/track/1b2jcwnf3JwuyWiG3xTPsp?si=abc123', 'https://open.spotify.com/embed/track/1b2jcwnf3JwuyWiG3xTPsp'],
            'locale prefix' => ['https://open.spotify.com/intl-id/track/1b2jcwnf3JwuyWiG3xTPsp', 'https://open.spotify.com/embed/track/1b2jcwnf3JwuyWiG3xTPsp'],
            'regional locale prefix' => ['https://open.spotify.com/intl-pt-BR/album/4aawyAB9vmqN3uQ7FjRGTy', 'https://open.spotify.com/embed/album/4aawyAB9vmqN3uQ7FjRGTy'],
            'embed link' => ['https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M?utm_source=generator', 'https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M'],
        ];
    }

    #[DataProvider('spotifyUrls')]
    public function test_spotify_embed_url_is_built(string $url, string $expected): void
    {
        $this->assertSame($expected, SpotifyHandler::embedUrl($url));
    }

    public function test_spotify_post_rejects_non_spotify_links(): void
    {
        foreach (['https://evil.example/open.spotify.com/track/abc', 'https://open.spotify.com/user/someone'] as $url) {
            Livewire::actingAs(User::factory()->create(['is_admin' => true]))
                ->test(PostForm::class)
                ->set('type', 'spotify')
                ->set('typeData', ['spotify_url' => $url])
                ->call('save')
                ->assertHasErrors(['typeData.spotify_url']);
        }
    }
}
