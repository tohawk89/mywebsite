<?php

namespace App\PostTypes\Profile;

use App\Enums\SnsType;
use App\Models\Post;
use App\PostTypes\PostTypeHandler;
use Illuminate\Validation\Rule;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'Profile';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'typeData.sns' => ['nullable', 'array'],
            'typeData.sns.*.platform' => ['required', Rule::enum(SnsType::class)],
            'typeData.sns.*.url' => ['required', 'url', 'max:500'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        return [
            'sns' => $typeData['sns'] ?? [],
        ];
    }

    public function mountData(Post $post): array
    {
        return [
            'sns' => $post->meta_data['sns'] ?? [],
        ];
    }

    public function mediaCollections(): array
    {
        return ['avatar'];
    }
}
