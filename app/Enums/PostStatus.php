<?php

namespace App\Enums;

enum PostStatus: int
{
    case DRAFT = 0;
    case PENDING = 1;
    case APPROVED = 2;
    case DECLINED = 3;
    case INACTIVE = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::DECLINED => 'Declined',
            self::INACTIVE => 'Inactive',
        };
    }
}
