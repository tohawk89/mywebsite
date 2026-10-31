<?php

namespace App\PostTypes\Image;

use App\Models\Post;
use App\PostTypes\HandlesMedia;
use App\PostTypes\PostTypeHandler;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Image posts hold one or more photos in the "gallery" media collection.
 * Older posts stored a single photo in "cover"; it moves into the gallery on the next save.
 */
class Handler implements HandlesMedia, PostTypeHandler
{
    public const COLLECTION = 'gallery';

    public const MAX_IMAGES = 20;

    public function label(): string
    {
        return 'Image';
    }

    public function icon(): string
    {
        return 'bi-images';
    }

    public function rules(): array
    {
        return [
            'typeData.images' => ['nullable', 'array', 'max:'.self::MAX_IMAGES],
            'typeData.images.*' => ['image', 'max:5120'],
            'typeData.remove' => ['nullable', 'array'],
            'typeData.remove.*' => ['integer'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        return [];
    }

    public function mountData(Post $post): array
    {
        return [
            'images' => [],
            'remove' => [],
            'existing' => self::images($post)
                ->map(fn (Media $media) => ['id' => $media->id, 'url' => $media->getUrl()])
                ->values()
                ->all(),
        ];
    }

    public function mediaCollections(): array
    {
        // Uploads are handled by saveMedia() and the type's own form.
        return [];
    }

    public function saveMedia(Post $post, array $typeData): void
    {
        // Move a legacy single "cover" photo into the gallery so everything lives in one collection.
        $post->getMedia('cover')->each(function (Media $media) {
            $media->collection_name = self::COLLECTION;
            $media->save();
        });

        $removeIds = array_map('intval', $typeData['remove'] ?? []);
        if ($removeIds !== []) {
            $post->getMedia(self::COLLECTION)->whereIn('id', $removeIds)->each->delete();
        }

        foreach ($typeData['images'] ?? [] as $upload) {
            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $post->addMediaFromDisk(
                FileUploadConfiguration::path($upload->getFilename(), false),
                FileUploadConfiguration::disk()
            )
                ->usingFileName($upload->getClientOriginalName())
                ->toMediaCollection(self::COLLECTION);
        }
    }

    /**
     * The post's photos in order: the gallery, or the legacy cover for posts not yet re-saved.
     *
     * @return Collection<int, Media>
     */
    public static function images(Post $post): Collection
    {
        $gallery = $post->getMedia(self::COLLECTION);

        return $gallery->isNotEmpty() ? $gallery : $post->getMedia('cover');
    }
}
