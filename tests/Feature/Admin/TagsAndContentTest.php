<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\TagIndex;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagsAndContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function tag(string $name): Tag
    {
        return Tag::create(['name' => $name, 'slug' => Tag::slugFor($name)]);
    }

    public function test_tags_page_lists_tags_with_post_counts(): void
    {
        $laravel = $this->tag('Laravel');
        $this->tag('Unused');
        Post::factory()->count(2)->create()->each(fn (Post $post) => $post->tags()->attach($laravel));

        $this->actingAs($this->admin())
            ->get(route('tags.index'))
            ->assertOk()
            ->assertSeeInOrder(['Laravel', '2 posts', 'Unused'])
            ->assertSee(route('posts.index', ['tag' => 'laravel']), false)
            ->assertSee('Delete 1 unused');
    }

    public function test_tags_can_be_searched(): void
    {
        $this->tag('Laravel');
        $this->tag('Music');

        Livewire::actingAs($this->admin())->test(TagIndex::class)
            ->set('search', 'lar')
            ->assertSee('Laravel')
            ->assertDontSee('Music');
    }

    public function test_tag_can_be_renamed(): void
    {
        $tag = $this->tag('Larvel');

        Livewire::actingAs($this->admin())->test(TagIndex::class)
            ->call('edit', $tag->id)
            ->set('editingName', 'Laravel')
            ->call('rename')
            ->assertHasNoErrors()
            ->assertSet('editingId', null);

        $this->assertSame(['Laravel', 'laravel'], [$tag->fresh()->name, $tag->fresh()->slug]);
    }

    public function test_renaming_to_an_existing_tag_merges_them(): void
    {
        $target = $this->tag('Laravel');
        $duplicate = $this->tag('Laravel PHP');
        $both = Post::factory()->create();
        $both->tags()->attach([$target->id, $duplicate->id]);
        $onlyDuplicate = Post::factory()->create();
        $onlyDuplicate->tags()->attach($duplicate);

        Livewire::actingAs($this->admin())->test(TagIndex::class)
            ->call('edit', $duplicate->id)
            ->set('editingName', 'laravel')
            ->call('rename')
            ->assertDispatched('notify', message: 'Merged into Laravel.');

        $this->assertModelMissing($duplicate);
        $this->assertSame(2, $target->posts()->count());
        $this->assertSame(['Laravel'], $onlyDuplicate->fresh()->tags->pluck('name')->all());
    }

    public function test_tags_can_be_deleted(): void
    {
        $used = $this->tag('Used');
        $post = Post::factory()->create();
        $post->tags()->attach($used);
        $this->tag('Unused A');
        $this->tag('Unused B');

        $component = Livewire::actingAs($this->admin())->test(TagIndex::class)
            ->call('deleteUnused')
            ->assertDispatched('notify', message: 'Deleted 2 unused tags.');
        $this->assertSame(['Used'], Tag::pluck('name')->all());

        $component->call('delete', $used->id);
        $this->assertSame(0, Tag::count());
        $this->assertCount(0, $post->fresh()->tags);
    }

    public function test_posts_can_be_filtered_by_tag(): void
    {
        $tag = $this->tag('Laravel');
        Post::factory()->create(['title' => 'Tagged post'])->tags()->attach($tag);
        Post::factory()->create(['title' => 'Other post']);

        Livewire::actingAs($this->admin())->withQueryParams(['tag' => 'laravel'])->test(PostIndex::class)
            ->assertSee('Tagged post')
            ->assertDontSee('Other post')
            ->call('clearFilters')
            ->assertSee('Other post');
    }

    public function test_tag_picker_offers_existing_tags(): void
    {
        $this->tag('Laravel');
        $this->tag('Music');

        Livewire::actingAs($this->admin())->test(PostForm::class)
            ->assertSeeHtml('tag-picker')
            ->assertSet('allTagNames', ['Laravel', 'Music']);
    }

    public function test_sidebar_links_to_tags(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard'))
            ->assertSee('href="'.route('tags.index').'"', false);
    }

    public function test_blog_and_page_use_the_markdown_editor(): void
    {
        $component = Livewire::actingAs($this->admin())->test(PostForm::class);

        foreach (['blog', 'page'] as $type) {
            $component->set('type', $type)->assertSeeHtml('class="markdown-editor"');
        }

        $component->set('type', 'quote')->assertDontSeeHtml('class="markdown-editor"');
    }

    public function test_markdown_content_is_rendered_safely(): void
    {
        $post = Post::factory()->make([
            'content' => "# Heading\n\n**Bold** and [a link](https://example.com)\nnext line\n\n<script>alert(1)</script>\n\n[bad](javascript:alert(1))",
        ]);

        $html = (string) $post->renderedContent();

        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringContainsString('<strong>Bold</strong>', $html);
        $this->assertStringContainsString('<a href="https://example.com">a link</a><br>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_excerpts_drop_markdown_formatting(): void
    {
        $post = Post::factory()->make(['content' => "## Title\n\nSome **bold** text with a [link](https://example.com)."]);

        $this->assertSame('Title Some bold text with a link.', $post->excerpt());
        $this->assertSame('Title Some...', $post->excerpt(10));
    }

    public function test_post_dialog_shows_rendered_markdown(): void
    {
        $post = Post::factory()->create(['content' => 'Hello **world**', 'posted_at' => now()->subMinute()]);

        Livewire::test('nav-modal')
            ->dispatch('open-modal', id: $post->id)
            ->assertSeeHtml('<strong>world</strong>');
    }

    public function test_youtube_and_spotify_posts_can_have_title_and_content(): void
    {
        foreach ([
            'youtube' => ['youtube_url' => 'https://youtu.be/dQw4w9WgXcQ'],
            'spotify' => ['spotify_url' => 'https://open.spotify.com/track/abc'],
        ] as $type => $typeData) {
            Livewire::actingAs($this->admin())->test(PostForm::class)
                ->set('type', $type)
                ->assertSee('Content')
                ->set('typeData', $typeData)
                ->set('title', "My {$type} pick")
                ->set('content', "Why I like this {$type} one.")
                ->call('save')
                ->assertHasNoErrors();
        }

        $this->get('/')
            ->assertSee('My youtube pick')
            ->assertSee('Why I like this youtube one.')
            ->assertSee('Why I like this spotify one.');
    }
}
