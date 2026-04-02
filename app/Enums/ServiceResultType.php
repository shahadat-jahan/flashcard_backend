<?php

namespace App\Enums;

enum ServiceResultType: int
{
    case DATA = 1;
    case META = 2;
    case JSON = 3;
    case DELETE = 4;
    case ERROR = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::DATA => 'data',
            self::META => 'meta',
            self::JSON => 'json',
            self::DELETE => 'delete',
            self::ERROR => 'error',
        };
    }
}
