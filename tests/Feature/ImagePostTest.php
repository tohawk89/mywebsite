<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use App\PostTypes\Image\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class ImagePostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
        Storage::fake('local');
    }

    private function form(?Post $post = null): Testable
    {
        return Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class, $post ? ['post' => $post] : []);
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function photos(int $count): array
    {
        return array_map(fn ($i) => UploadedFile::fake()->image("photo-{$i}.jpg"), range(1, $count));
    }

    public function test_multiple_photos_can_be_uploaded(): void
    {
        $this->form()
            ->set('type', 'image')
            ->set('title', 'Zoo trip')
            ->set('typeData.images', $this->photos(3))
            ->call('save')
            ->assertHasNoErrors();

        $post = Post::first();
        $this->assertCount(3, $post->getMedia(Handler::COLLECTION));
        $this->assertSame(
            ['photo-1.jpg', 'photo-2.jpg', 'photo-3.jpg'],
            $post->getMedia(Handler::COLLECTION)->pluck('file_name')->all(),
        );
    }

    public function test_feed_card_shows_count_and_opens_all_photos_fullscreen(): void
    {
        $this->form()->set('type', 'image')->set('typeData.images', $this->photos(3))->call('save');

        $response = $this->get('/')->assertOk()
            ->assertSee('image-post-count', false)
            ->assertSee('window.openLightbox(', false);

        $response->assertDontSee('@js', false);

        foreach (Post::first()->getMedia(Handler::COLLECTION) as $media) {
            $response->assertSee($media->file_name, false);
        }
    }

    public function test_single_photo_card_has_no_count_badge(): void
    {
        $this->form()->set('type', 'image')->set('typeData.images', $this->photos(1))->call('save');

        $this->get('/')->assertSee('window.openLightbox(', false)->assertDontSee('image-post-count', false);
    }

    public function test_photos_can_be_removed_and_added_when_editing(): void
    {
        $this->form()->set('type', 'image')->set('typeData.images', $this->photos(3))->call('save');
        $post = Post::first();
        $first = $post->getMedia(Handler::COLLECTION)->first();

        $this->form($post)
            ->assertSet('typeData.existing.0.id', $first->id)
            ->set('typeData.remove', [(string) $first->id])
            ->set('typeData.images', [UploadedFile::fake()->image('extra.jpg')])
            ->call('save')
            ->assertHasNoErrors();

        $names = $post->fresh()->getMedia(Handler::COLLECTION)->pluck('file_name')->all();
        $this->assertSame(['photo-2.jpg', 'photo-3.jpg', 'extra.jpg'], $names);
    }

    public function test_legacy_cover_photo_is_shown_and_moved_into_the_gallery_on_save(): void
    {
        $post = Post::factory()->create(['type' => 'image', 'posted_at' => now()->subMinute()]);
        $post->addMedia(UploadedFile::fake()->image('old-cover.jpg'))->toMediaCollection('cover');

        $this->assertCount(1, Handler::images($post));
        $this->get('/')->assertSee('old-cover.jpg');

        $this->form($post)->call('save')->assertHasNoErrors();

        $post->refresh();
        $this->assertCount(0, $post->getMedia('cover'));
        $this->assertSame(['old-cover.jpg'], $post->getMedia(Handler::COLLECTION)->pluck('file_name')->all());
    }

    public function test_only_images_are_accepted(): void
    {
        $this->form()
            ->set('type', 'image')
            ->set('typeData.images', [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['typeData.images.0']);
    }

    public function test_photo_count_is_limited(): void
    {
        $this->form()
            ->set('type', 'image')
            ->set('typeData.images', $this->photos(Handler::MAX_IMAGES + 1))
            ->call('save')
            ->assertHasErrors(['typeData.images']);
    }

    public function test_other_post_types_keep_their_cover_upload(): void
    {
        $this->form()
            ->set('type', 'blog')
            ->assertSee('Cover image')
            ->set('type', 'image')
            ->assertDontSee('Cover image')
            ->assertSee('Photos');
    }
}
