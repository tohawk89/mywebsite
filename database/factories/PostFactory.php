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
}
