<?php

namespace App\Enums;

enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }
}
