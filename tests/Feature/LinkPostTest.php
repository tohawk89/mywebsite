<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use App\PostTypes\Link\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LinkPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pretend every hostname is public unless a test says otherwise, so tests need no DNS.
        Handler::$resolveHostUsing = fn (string $host) => $host === 'intranet.example' ? ['10.0.0.5'] : ['93.184.216.34'];
    }

    protected function tearDown(): void
    {
        Handler::$resolveHostUsing = null;

        parent::tearDown();
    }

    private function share(string $url, array $fields = []): Testable
    {
        $component = Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class)
            ->set('type', 'link')
            ->set('typeData', ['url' => $url]);

        foreach ($fields as $field => $value) {
            $component->set($field, $value);
        }

        return $component->call('save');
    }

    private function fakePage(string $html, string $contentType = 'text/html; charset=utf-8'): void
    {
        Http::fake(['*' => Http::response($html, 200, ['Content-Type' => $contentType])]);
    }

    public function test_open_graph_details_are_read_from_the_page(): void
    {
        $this->fakePage(<<<'HTML'
            <html><head>
                <title>Fallback title</title>
                <meta property="og:title" content="Laravel 13 is here">
                <meta property="og:description" content="Everything new in this release.">
                <meta property="og:image" content="/images/cover.png">
                <meta property="og:site_name" content="Laravel News">
                <link rel="Shortcut Icon" href="favicon-32.png">
            </head><body></body></html>
            HTML);

        $this->share('https://www.laravel-news.com/posts/laravel-13')->assertHasNoErrors();

        $meta = Post::first()->meta_data;
        $this->assertSame('https://www.laravel-news.com/posts/laravel-13', $meta['url']);
        $this->assertSame('laravel-news.com', $meta['domain']);
        $this->assertSame('Laravel 13 is here', $meta['page_title']);
        $this->assertSame('Everything new in this release.', $meta['description']);
        $this->assertSame('https://www.laravel-news.com/images/cover.png', $meta['image']);
        $this->assertSame('Laravel News', $meta['site_name']);
        $this->assertSame('https://www.laravel-news.com/posts/favicon-32.png', $meta['favicon']);
    }

    public function test_falls_back_to_title_tag_and_meta_description(): void
    {
        $this->fakePage('<html><head><title> Plain page </title><meta name="description" content="A simple page."></head></html>');

        $this->share('https://example.com')->assertHasNoErrors();

        $meta = Post::first()->meta_data;
        $this->assertSame('Plain page', $meta['page_title']);
        $this->assertSame('A simple page.', $meta['description']);
        $this->assertNull($meta['image']);
        $this->assertSame('https://example.com/favicon.ico', $meta['favicon']);
    }

    public function test_unreachable_or_non_html_pages_still_save_the_link(): void
    {
        $this->fakePage('%PDF-1.7', 'application/pdf');

        $this->share('https://example.com/file.pdf')->assertHasNoErrors();

        $meta = Post::first()->meta_data;
        $this->assertSame('https://example.com/file.pdf', $meta['url']);
        $this->assertArrayNotHasKey('page_title', $meta);
    }

    public static function privateUrls(): array
    {
        return [
            'localhost ip' => ['http://127.0.0.1/admin'],
            'private range' => ['http://192.168.1.10'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data'],
            'host resolving to private ip' => ['https://intranet.example/secret'],
        ];
    }

    #[DataProvider('privateUrls')]
    public function test_private_network_addresses_are_never_fetched(string $url): void
    {
        Http::fake();

        $this->share($url)->assertHasNoErrors();

        Http::assertNothingSent();
        $this->assertSame($url, Post::first()->meta_data['url']);
    }

    public function test_url_must_be_http_or_https(): void
    {
        Http::fake();

        $this->share('ftp://example.com/file')->assertHasErrors(['typeData.url']);
        $this->share('')->assertHasErrors(['typeData.url']);
    }

    public function test_title_and_content_are_optional_and_shown_above_the_preview(): void
    {
        $this->fakePage('<html><head><meta property="og:title" content="Shared article"></head></html>');

        $this->share('https://example.com/a', ['title' => 'Worth a read', 'content' => 'Great breakdown of the topic.'])
            ->assertHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Worth a read', 'Great breakdown of the topic.', 'Shared article', 'example.com'])
            ->assertSee('href="https://example.com/a"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_card_works_without_title_content_or_preview_data(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->share('https://example.com/down')->assertHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSee('link-preview', false)
            ->assertSee('https://example.com/down');
    }

    public function test_editing_prefills_the_url(): void
    {
        $post = Post::factory()->create(['type' => 'link', 'meta_data' => ['url' => 'https://example.com/x']]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class, ['post' => $post])
            ->assertSet('typeData.url', 'https://example.com/x');
    }

    public function test_long_caption_is_shown_in_full_with_a_read_more_toggle(): void
    {
        $caption = str_repeat('A long thought about this article. ', 30).'THE-END';

        Post::factory()->create([
            'type' => 'link',
            'content' => $caption,
            'meta_data' => ['url' => 'https://example.com/long'],
            'posted_at' => now()->subMinute(),
        ]);

        $this->get('/')->assertOk()
            ->assertSee('THE-END')
            ->assertSee('Read more')
            ->assertSee('card-read-more', false);
    }
}
