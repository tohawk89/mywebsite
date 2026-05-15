<?php

namespace App\PostTypes;

use App\Models\Post;

interface PostTypeHandler
{
    /** Human-readable label shown in the type selector. */
    public function label(): string;

    /**
     * Extra validation rules merged with PostForm's base rules.
     * Keys for type-specific fields must be prefixed with 'typeData.'.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array;

    /**
     * Build the meta_data array to persist from type-specific form values.
     *
     * @param  array<string, mixed>  $typeData
     * @return array<string, mixed>
     */
    public function buildMetaData(array $typeData): array;

    /**
     * Extract initial typeData values from an existing Post.
     *
     * @return array<string, mixed>
     */
    public function mountData(Post $post): array;

    /**
     * Media collection names this type uses, e.g. ['cover'] or ['avatar'].
     *
     * @return string[]
     */
    public function mediaCollections(): array;
}
