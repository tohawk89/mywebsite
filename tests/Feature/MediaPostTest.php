<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
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
}
