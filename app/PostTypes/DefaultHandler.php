<?php

namespace App\PostTypes;

use App\Models\Post;

class DefaultHandler implements PostTypeHandler
{
    public function __construct(private string $type) {}

    public function label(): string
    {
        return ucfirst($this->type);
    }

    public function rules(): array
    {
        return [];
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
