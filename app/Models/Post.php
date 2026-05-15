<?php

namespace App\Models;

// use App\Enums\PostType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'type',
        'title',
        'content',
        'meta_data',
        'is_pinned',
        'is_draft',
        'sort_order',
        'posted_at',
    ];

    protected $casts = [
        // 'type' => PostType::class,
        'meta_data' => 'array',
        'is_pinned' => 'boolean',
        'is_draft' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile();

        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
