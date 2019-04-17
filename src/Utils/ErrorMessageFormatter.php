<?php

declare(strict_types=1);

namespace App\Utils;

class ErrorMessageFormatter
{
    public static function errorFormat(\Exception $exception, string $internalCode = null): array
    {
        return [
            'error' => [
                'message' => $exception->getMessage(),
                'type' => get_class($exception),
                'code' => $internalCode ?: $exception->getCode(),
            ],
        ];
    }
}