<?php

namespace App\PostTypes\Project;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Project';
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
        return ['cover'];
    }
}
