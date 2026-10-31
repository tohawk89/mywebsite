<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::values()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        if (app()->bound('settings.values')) {
            app()->instance('settings.values', [...app('settings.values'), $key => $value]);
        }
    }

    /**
     * All settings, loaded with one query per request (kept in the container, so tests start fresh).
     *
     * @return array<string, mixed>
     */
    protected static function values(): array
    {
        if (! app()->bound('settings.values')) {
            app()->instance('settings.values', static::query()->pluck('value', 'key')->all());
        }

        return app('settings.values');
    }

    /**
     * Return all settings as a key=>value array.
     *
     * @return array<string, mixed>
     */
    public static function map(): array
    {
        return static::all()->pluck('value', 'key')->all();
    }
}
