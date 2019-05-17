<?php

declare(strict_types=1);

namespace App\Utils;


use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorMessageFormatter
{
    public static function errorFormat(\Exception $exception, string $internalCode = null): array
    {
        $code = self::getCode($exception, $internalCode);

        return [
            'error' => [
                'message' => $exception->getMessage(),
                'type' => get_class($exception),
                'code' => $code,
            ],
        ];
    }

    /**
     * @param \Exception $exception
     * @param string|null $internalCode
     * @return int|string
     */
    private static function getCode(\Exception $exception, string $internalCode = null)
    {
        switch (true) {
            case $internalCode:
                $code = $internalCode;
                break;
            case $exception instanceof HttpExceptionInterface:
                $code = $exception->getStatusCode();
                break;
            default:
                $code = $exception->getCode();
        }

        return $code;
    }
}