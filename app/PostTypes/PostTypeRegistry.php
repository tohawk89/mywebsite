<?php

namespace App\PostTypes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Discovers post types from resources/views/components/posts/{type} folders
 * and resolves each one to its Handler (App\PostTypes\{StudlyType}\Handler).
 */
class PostTypeRegistry
{
    /** Type names must be kebab-case so they work as folder, component and class names. */
    public const NAME_PATTERN = '/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/';

    public static function path(string $type = ''): string
    {
        return resource_path('views/components/posts'.($type !== '' ? '/'.$type : ''));
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return collect(File::directories(self::path()))
            ->map(fn (string $dir) => basename($dir))
            ->sort()
            ->values()
            ->all();
    }

    public static function exists(string $type): bool
    {
        return File::isDirectory(self::path($type));
    }

    public static function handlerClass(string $type): string
    {
        return 'App\\PostTypes\\'.Str::studly($type).'\\Handler';
    }

    public static function handler(string $type): PostTypeHandler
    {
        $class = self::handlerClass($type);

        return class_exists($class) ? new $class : new DefaultHandler($type);
    }
}
