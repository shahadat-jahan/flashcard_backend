<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

abstract class Service
{
    protected ServiceResult $result;

    public function __construct()
    {
        $this->result = new ServiceResult;
    }

    protected function keepLog(string $type, string $message, ?string $exception = null): void
    {
        $context = $exception ? ['exception' => $exception] : [];

        switch (strtolower($type)) {
            case 'debug':
                Log::debug($message, $context);
                break;
            case 'info':
                Log::info($message, $context);
                break;
            case 'warning':
                Log::warning($message, $context);
                break;
            case 'error':
            default:
                Log::error($message, $context);
                break;
        }
    }
}
