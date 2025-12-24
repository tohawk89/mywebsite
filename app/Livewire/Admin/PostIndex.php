<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class PostIndex extends Component
{
    use WithPagination;

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
    }

    public function render()
    {
        return view('livewire.admin.post-index', [
            'posts' => Post::orderByDesc('created_at')->paginate(20),
        ]);
    }
}
