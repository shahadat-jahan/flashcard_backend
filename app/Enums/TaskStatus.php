<?php

namespace App\Enums;

enum TaskStatus: int
{
    case PENDING = 0;
    case SUBMITTED = 1;
    case SCHEDULED = 2;
    case PUBLISHED = 3;
    case APPROVED = 4;
    case DECLINED = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUBMITTED => 'Submitted',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHED => 'Published',
            self::APPROVED => 'Approved',
            self::DECLINED => 'Declined',
        };
    }
}
