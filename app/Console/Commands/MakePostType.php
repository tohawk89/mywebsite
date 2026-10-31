<?php

namespace App\Console\Commands;

use App\PostTypes\PostTypeRegistry;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class MakePostType extends Command
{
    /**
     * Media collections PostForm knows how to upload (see Post::registerMediaCollections()).
     */
    private const SUPPORTED_MEDIA = ['cover', 'avatar'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:post-type
        {name : The post type name in kebab-case, e.g. podcast or book-review}
        {--label= : The label shown in the admin type selector}
        {--media=* : Media collections the type uses (cover, avatar)}
        {--force : Overwrite the post type if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new post type (Handler, feed card and admin form)';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files, Composer $composer): int
    {
        $type = (string) $this->argument('name');

        if (! preg_match(PostTypeRegistry::NAME_PATTERN, $type)) {
            $this->error("Invalid post type name [{$type}]. Use kebab-case, e.g. podcast or book-review.");

            return self::FAILURE;
        }

        $media = array_values(array_unique($this->option('media')));
        $unsupported = array_diff($media, self::SUPPORTED_MEDIA);

        if ($unsupported !== []) {
            $this->error('Unsupported media collection ['.implode(', ', $unsupported).']. Supported: '.implode(', ', self::SUPPORTED_MEDIA).'.');

            return self::FAILURE;
        }

        if (PostTypeRegistry::exists($type) && ! $this->option('force')) {
            $this->error("Post type [{$type}] already exists. Use --force to overwrite it.");

            return self::FAILURE;
        }

        $directory = PostTypeRegistry::path($type);
        $replacements = [
            '{{ class }}' => Str::studly($type),
            '{{ label }}' => var_export($this->option('label') ?: Str::headline($type), true),
            '{{ mediaCollections }}' => $media === [] ? '[]' : "['".implode("', '", $media)."']",
        ];

        $files->ensureDirectoryExists($directory);

        foreach (['Handler.php' => 'Handler.stub', 'card.blade.php' => 'card.stub', 'form.blade.php' => 'form.stub'] as $file => $stub) {
            $contents = strtr($files->get(base_path("stubs/post-type/{$stub}")), $replacements);
            $files->put("{$directory}/{$file}", $contents);
        }

        // Handlers live under resources/ via Composer's classmap, so the autoloader must be rebuilt.
        $composer->dumpAutoloads();

        $this->components->info("Post type [{$type}] created in resources/views/components/posts/{$type}.");
        $this->components->bulletList([
            'Handler.php: label, validation rules and meta_data mapping',
            'card.blade.php: the public feed card',
            'form.blade.php: the admin form fields',
        ]);

        return self::SUCCESS;
    }
}
