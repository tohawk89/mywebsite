<?php

use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public bool $isOpen = false;

    public ?string $content = null;

    public ?string $title = null;

    public ?string $imageUrl = null;

    public array $tags = [];

    public ?string $postedAt = null;

    #[On('open-modal')]
    public function openModal(?int $id = null, ?string $component = null): void
    {
        $post = null;
        $this->imageUrl = null;
        $this->tags = [];
        $this->postedAt = null;

        if ($id) {
            $post = Post::with('tags')->find($id);
        } elseif ($component) {
            $post = Post::whereJsonContains('meta_data->slug', $component)->first();
        }

        if ($post) {
            $this->title = $post->title;
            $this->content = $post->content;
            $this->postedAt = $post->posted_at?->format('F d, Y');

            $this->tags = $post->tags->map(function ($tag) {
                $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary', 'dark'];
                $colorIndex = crc32($tag->name) % count($colors);

                return [
                    'name' => $tag->name,
                    'color' => $colors[$colorIndex],
                ];
            })->toArray();

            if ($post->hasMedia('cover')) {
                $this->imageUrl = $post->getFirstMediaUrl('cover');
            }

            $this->isOpen = true;
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->content = null;
        $this->title = null;
        $this->imageUrl = null;
        $this->tags = [];
        $this->postedAt = null;
    }
};
?>

<div>
    @if($isOpen)
        {{-- Backdrop --}}
        <div class="modal-backdrop fade show"></div>

        {{-- Modal --}}
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-scrollable modal-lg mt-5" role="document">
                <div class="modal-content border-0 shadow-lg rounded-0" @click.away="$wire.closeModal()">

                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" class="card-img-top rounded-0" alt="{{ $title }}"
                            style="max-height: 400px; object-fit: cover;">
                    @endif

                    <div class="modal-header border-0 pb-0 pt-4 px-4 d-block">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                @foreach($tags as $tag)
                                    <span
                                        class="badge bg-{{ $tag['color'] }}-subtle text-{{ $tag['color'] }} rounded-0 border border-{{ $tag['color'] }}-subtle me-1">
                                        {{ $tag['name'] }}
                                    </span>
                                @endforeach
                            </div>
                            @if($postedAt)
                                <small class="text-muted font-monospace text-uppercase" style="font-size: 0.75rem;">Posted on
                                    {{ $postedAt }}</small>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="modal-title fw-bold font-sans display-6 mb-0">{{ $title }}</h3>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                    </div>

                    <div class="modal-body px-4 pb-4 pt-2 post-content text-muted fs-5">
                        {!! nl2br(e($content)) !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
