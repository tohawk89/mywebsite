<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Unique key for a tag name: case-insensitive, keeps "C#" / "C++" distinct from "C",
     * and falls back to the lowercased name when it has no Latin characters to slug.
     */
    public static function slugFor(string $name): string
    {
        return Str::slug($name, '-', 'en', ['#' => 'sharp', '+' => 'plus', '@' => 'at'])
            ?: mb_strtolower(trim($name));
    }
}
