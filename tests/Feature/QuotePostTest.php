<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_card_shows_the_quote_and_its_author(): void
    {
        Post::factory()->create([
            'type' => 'quote',
            'title' => 'Steve Jobs',
            'content' => 'Stay hungry, stay foolish.',
            'posted_at' => now()->subMinute(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Stay hungry, stay foolish.', 'Steve Jobs'])
            ->assertSee('quote-text', false)
            ->assertDontSee('fs-4 fst-italic', false);
    }

    public function test_quote_without_author_has_no_caption(): void
    {
        Post::factory()->create(['type' => 'quote', 'title' => '', 'content' => 'Anonymous wisdom.', 'posted_at' => now()->subMinute()]);

        $this->get('/')->assertSee('Anonymous wisdom.')->assertDontSee('<figcaption', false);
    }
}
