<?php

namespace App\Enums;

enum PostType: int
{
    case BLOG = 1;
    case FLASHCARD = 2;
    case TWEET = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::BLOG => 'Blog',
            self::FLASHCARD => 'Flashcard',
            self::TWEET => 'Tweet',
        };
    }
}
