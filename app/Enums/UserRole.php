<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 1;
    case USER = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }
}
