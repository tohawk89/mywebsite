<?php

namespace App\PostTypes\Blog;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Blog Post';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        return [];
    }

    public function mountData(Post $post): array
    {
        return [];
    }

    public function mediaCollections(): array
    {
        return ['cover'];
    }
}
