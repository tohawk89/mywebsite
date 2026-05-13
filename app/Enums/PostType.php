<?php

namespace App\Enums;

enum PostType: string
{
    case BLOG = 'blog';
    case SPOTIFY = 'spotify';
    case YOUTUBE = 'youtube';
    case INSTAGRAM = 'instagram';
    case QUOTE = 'quote';
    case PAGE = 'page'; // For About, Contact
    case IMAGE = 'image';
    case PROJECT = 'project';
    case PROFILE = 'profile';
    case REPOSITORY = 'repository';

    public function label(): string
    {
        return match ($this) {
            self::BLOG => 'Blog Post',
            self::SPOTIFY => 'Spotify Embed',
            self::YOUTUBE => 'YouTube Video',
            self::INSTAGRAM => 'Instagram Post',
            self::QUOTE => 'Quote',
            self::PAGE => 'Page',
            self::IMAGE => 'Image',
            self::PROJECT => 'Project',
            self::PROFILE => 'Profile',
            self::REPOSITORY => 'Git Repository',
        };
    }
}
