<?php

declare(strict_types=1);

namespace App\Limiter\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class RestrictedException extends \Exception implements HttpExceptionInterface
{
    public function getStatusCode()
    {
        return 400;
    }

    public function getHeaders()
    {
        return [];
    }
}
