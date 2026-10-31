<?php

namespace App\Models;

// use App\Enums\PostType;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<PostFactory> */
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
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at');
    }

    /**
     * Posts visitors may see: not a draft and with a publish date that has arrived.
     */
    public function scopePublished($query)
    {
        return $query->where('is_draft', false)
            ->whereNotNull('posted_at')
            ->where('posted_at', '<=', now());
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
