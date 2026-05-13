<?php

namespace Database\Factories;

use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => PostType::BLOG,
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'meta_data' => [],
            'is_pinned' => false,
            'sort_order' => 0,
            'posted_at' => now(),
        ];
    }

    public function profile(): static
    {
        return $this->state([
            'type' => PostType::PROFILE,
            'meta_data' => ['sns' => []],
        ]);
    }

    public function repository(): static
    {
        return $this->state([
            'type' => PostType::REPOSITORY,
            'title' => 'laravel/framework',
            'content' => null,
            'meta_data' => [
                'repo_url' => 'https://github.com/laravel/framework',
                'repo_name' => 'laravel/framework',
                'repo_description' => 'The Laravel Framework.',
                'language' => 'PHP',
                'stars' => 33000,
                'forks' => 11000,
                'topics' => ['laravel', 'php', 'framework'],
                'owner_avatar' => 'https://avatars.githubusercontent.com/u/958072?v=4',
                'fetched_at' => now()->toIso8601String(),
                'title' => null,
                'note' => null,
            ],
        ]);
    }
}
