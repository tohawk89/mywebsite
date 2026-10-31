<?php

namespace App\PostTypes;

use App\Models\Post;

/**
 * Optional: for post types that manage their own uploads (e.g. a multi-image gallery)
 * instead of the shared single-file "cover" / "avatar" inputs.
 */
interface HandlesMedia
{
    /**
     * Called after the post (and its meta_data) is saved.
     *
     * @param  array<string, mixed>  $typeData  May contain Livewire TemporaryUploadedFile instances.
     */
    public function saveMedia(Post $post, array $typeData): void;
}
