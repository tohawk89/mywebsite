<?php

namespace App\PostTypes\Quote;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Quote';
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'title' => ['required', 'string'],
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
        return [];
    }
}
