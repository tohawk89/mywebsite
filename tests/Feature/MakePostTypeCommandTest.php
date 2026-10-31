<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostForm;
use App\Models\Post;
use App\Models\User;
use App\PostTypes\PostTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class MakePostTypeCommandTest extends TestCase
{
    use RefreshDatabase;

    private const TYPE = 'generator-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(Composer::class, Mockery::mock(Composer::class, function ($mock) {
            $mock->shouldReceive('dumpAutoloads')->zeroOrMoreTimes();
        }));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(PostTypeRegistry::path(self::TYPE));

        parent::tearDown();
    }

    public function test_it_creates_the_post_type_files(): void
    {
        $this->artisan('make:post-type', ['name' => self::TYPE, '--label' => "Gen's Test", '--media' => ['cover']])
            ->assertSuccessful();

        $handler = File::get(PostTypeRegistry::path(self::TYPE).'/Handler.php');

        $this->assertStringContainsString('namespace App\PostTypes\GeneratorTest;', $handler);
        $this->assertStringContainsString("return 'Gen\\'s Test';", $handler);
        $this->assertStringContainsString("return ['cover'];", $handler);
        $this->assertFileExists(PostTypeRegistry::path(self::TYPE).'/card.blade.php');
        $this->assertFileExists(PostTypeRegistry::path(self::TYPE).'/form.blade.php');
        $this->assertContains(self::TYPE, PostTypeRegistry::all());
    }

    public function test_label_defaults_to_headline_of_name(): void
    {
        $this->artisan('make:post-type', ['name' => self::TYPE])->assertSuccessful();

        $this->assertStringContainsString(
            "return 'Generator Test';",
            File::get(PostTypeRegistry::path(self::TYPE).'/Handler.php'),
        );
    }

    public function test_generated_post_type_works_end_to_end(): void
    {
        $this->artisan('make:post-type', ['name' => self::TYPE])->assertSuccessful();

        // Composer is mocked, so load the generated handler the way the classmap would.
        require_once PostTypeRegistry::path(self::TYPE).'/Handler.php';

        $this->assertSame('Generator Test', PostTypeRegistry::handler(self::TYPE)->label());

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))
            ->test(PostForm::class)
            ->set('type', self::TYPE)
            ->set('title', 'Generated Post')
            ->set('typeData', ['subtitle' => 'From the stub'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['subtitle' => 'From the stub'], Post::first()->meta_data);

        $this->get('/')->assertSee('Generated Post')->assertSee('From the stub');
    }

    public function test_it_rejects_invalid_names(): void
    {
        $this->artisan('make:post-type', ['name' => 'Bad_Name'])->assertFailed();

        $this->assertDirectoryDoesNotExist(PostTypeRegistry::path('Bad_Name'));
    }

    public function test_it_rejects_unsupported_media(): void
    {
        $this->artisan('make:post-type', ['name' => self::TYPE, '--media' => ['gallery']])->assertFailed();

        $this->assertDirectoryDoesNotExist(PostTypeRegistry::path(self::TYPE));
    }

    public function test_it_refuses_to_overwrite_without_force(): void
    {
        $this->artisan('make:post-type', ['name' => self::TYPE])->assertSuccessful();
        File::put(PostTypeRegistry::path(self::TYPE).'/card.blade.php', 'custom');

        $this->artisan('make:post-type', ['name' => self::TYPE])->assertFailed();
        $this->assertSame('custom', File::get(PostTypeRegistry::path(self::TYPE).'/card.blade.php'));

        $this->artisan('make:post-type', ['name' => self::TYPE, '--force' => true])->assertSuccessful();
        $this->assertNotSame('custom', File::get(PostTypeRegistry::path(self::TYPE).'/card.blade.php'));
    }

    public function test_existing_types_resolve_through_the_registry(): void
    {
        $this->assertSame('YouTube Video', PostTypeRegistry::handler('youtube')->label());
        $this->assertSame('App\PostTypes\BookReview\Handler', PostTypeRegistry::handlerClass('book-review'));
        $this->assertSame('Page', PostTypeRegistry::handler('page')->label());
    }
}
