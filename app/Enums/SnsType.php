<?php

namespace App\Enums;

enum SnsType: string
{
    case GitHub = 'github';
    case Twitter = 'twitter';
    case LinkedIn = 'linkedin';
    case Instagram = 'instagram';
    case YouTube = 'youtube';
    case TikTok = 'tiktok';
    case Facebook = 'facebook';
    case Discord = 'discord';
    case Twitch = 'twitch';
    case Telegram = 'telegram';
    case Reddit = 'reddit';
    case Website = 'website';

    public function label(): string
    {
        return match ($this) {
            self::GitHub => 'GitHub',
            self::Twitter => 'X / Twitter',
            self::LinkedIn => 'LinkedIn',
            self::Instagram => 'Instagram',
            self::YouTube => 'YouTube',
            self::TikTok => 'TikTok',
            self::Facebook => 'Facebook',
            self::Discord => 'Discord',
            self::Twitch => 'Twitch',
            self::Telegram => 'Telegram',
            self::Reddit => 'Reddit',
            self::Website => 'Website',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GitHub => 'bi-github',
            self::Twitter => 'bi-twitter-x',
            self::LinkedIn => 'bi-linkedin',
            self::Instagram => 'bi-instagram',
            self::YouTube => 'bi-youtube',
            self::TikTok => 'bi-tiktok',
            self::Facebook => 'bi-facebook',
            self::Discord => 'bi-discord',
            self::Twitch => 'bi-twitch',
            self::Telegram => 'bi-telegram',
            self::Reddit => 'bi-reddit',
            self::Website => 'bi-globe',
        };
    }
}
