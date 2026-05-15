<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SettingSeeder::class);

        // Create Tags
        $tags = ['Design', 'Code', 'Music', 'Life', 'Tech', 'Art', 'Nature'];
        $tagModels = collect();
        foreach ($tags as $tag) {
            $tagModels->push(Tag::create(['name' => $tag, 'slug' => \Illuminate\Support\Str::slug($tag)]));
        }

        // About Page
        Post::create([
            'type' => 'page',
            'title' => 'About Me',
            'content' => 'Hello! I am NS, a passionate developer and creator. Welcome to my digital garden.',
            'is_pinned' => false,
            'meta_data' => ['slug' => 'about'],
        ]);

        // Contact Page
        Post::create([
            'type' => 'page',
            'title' => 'Contact',
            'content' => 'Reach out to me at hello@example.com or follow me on social media.',
            'is_pinned' => false,
            'meta_data' => ['slug' => 'contact'],
        ]);

        // Profile Card (Pinned)
        Post::create([
            'type' => 'blog',
            'title' => 'Welcome to my World',
            'content' => 'This is the start of something new. Creating a masonry layout blog with Laravel Livewire.',
            'is_pinned' => true,
            'sort_order' => 1,
        ]);

        // Spotify Embed (Green Card)
        Post::create([
            'type' => 'spotify',
            'title' => 'Coding Vibes',
            'meta_data' => ['url' => 'https://open.spotify.com/album/1DFixLWuPkv3KT3TnV35m3'],
            'is_pinned' => true,
            'sort_order' => 2,
        ])->tags()->attach($tagModels->where('name', 'Music')->first()->id);

        // YouTube Embed (Red Card)
        Post::create([
            'type' => 'youtube',
            'title' => 'Inspiration',
            'meta_data' => ['video_id' => 'jfKfPfyJRdk'],
            'is_pinned' => true,
            'sort_order' => 3,
        ])->tags()->attach($tagModels->where('name', 'Music')->first()->id);

        // Create a Profile Post
        $profilePost = Post::create([
            'type' => 'profile',
            'title' => 'Nazar',
            'content' => 'Full Stack Developer | Laravel Enthusiast | Open Source Contributor',
            'meta_data' => [
                'avatar' => 'https://ui-avatars.com/api/?name=Nazar&background=random&size=200',
                'links' => [
                    ['url' => '#', 'icon' => 'bi-twitter-x', 'label' => 'Twitter'],
                    ['url' => '#', 'icon' => 'bi-github', 'label' => 'GitHub'],
                    ['url' => '#', 'icon' => 'bi-linkedin', 'label' => 'LinkedIn'],
                    ['url' => '#', 'icon' => 'bi-spotify', 'label' => 'Spotify'],
                ],
            ],
            'is_pinned' => true,
            'sort_order' => 1,
            'posted_at' => now(),
        ]);
        $profilePost->tags()->attach($tagModels->random(2));

        // Create an Image Post
        $imagePost = Post::create([
            'type' => 'image',
            'title' => 'Design Inspiration', // Title might not be shown, but good for DB
            'content' => 'Beautiful minimal setup.',
            'posted_at' => now(),
        ]);
        $imagePost->tags()->attach($tagModels->random(1));
        try {
            $imagePost->addMediaFromUrl('https://picsum.photos/seed/img'.rand(1, 1000).'/800/600')->toMediaCollection('cover');
        } catch (\Exception $e) {
            // connection error handling
        }

        // Create a YouTube Post
        $youtubePost = Post::create([
            'type' => 'youtube',
            'title' => 'Laravel 11 Features',
            'content' => 'Checking out the new features in Laravel 11.',
            'meta_data' => [
                'embed_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Classic
            ],
            'posted_at' => now(),
        ]);
        $youtubePost->tags()->attach($tagModels->random(1));

        // Quote (Yellow Card)
        Post::create([
            'type' => 'quote',
            'content' => 'Simplicity is the ultimate sophistication.',
            'meta_data' => ['author' => 'Leonardo da Vinci'],
            'sort_order' => 4,
        ])->tags()->attach($tagModels->where('name', 'Life')->first()->id);

        // Random Blog Posts with Images
        for ($i = 0; $i < 12; $i++) {
            $post = Post::create([
                'type' => 'blog',
                'title' => 'Blog Post '.$i,
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'posted_at' => now()->subDays(rand(1, 30)),
            ]);

            // Attach random tags
            $post->tags()->attach($tagModels->random(rand(1, 2))->pluck('id'));

            // Attach Media (Picsum)
            try {
                $post->addMediaFromUrl('https://picsum.photos/seed/'.($i + 100).'/600/400')
                    ->toMediaCollection('cover');
            } catch (\Throwable $e) {
                echo "Failed to attach media to post {$post->id}: ".$e->getMessage().PHP_EOL;
            }
        }
    }
}
