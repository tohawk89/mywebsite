<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Component;

class NavModal extends Component
{
    public $isOpen = false;
    public $content = null;
    public $title = null;
    public $imageUrl = null;
    public $tags = [];
    public $postedAt = null;

    #[On('open-modal')]
    public function openModal($id = null, $component = null)
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

            // Map tags
            $this->tags = $post->tags->map(function ($tag) {
                $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary', 'dark'];
                $colorIndex = crc32($tag->name) % count($colors);
                return [
                    'name' => $tag->name,
                    'color' => $colors[$colorIndex],
                ];
            })->toArray();

            // Handle Covers or Full Images
            if ($post->hasMedia('cover')) {
                // For IMAGE type, we might want a bigger/different URL, 
                // but getFirstMediaUrl('cover') works if we just uploaded one image.
                $this->imageUrl = $post->getFirstMediaUrl('cover');
            }

            $this->isOpen = true;
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->content = null;
        $this->title = null;
        $this->imageUrl = null;
        $this->tags = [];
        $this->postedAt = null;
    }

    public function render()
    {
        return view('livewire.nav-modal');
    }
}
