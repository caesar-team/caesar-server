<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ApiException extends \Exception
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [], int $code = Response::HTTP_BAD_REQUEST)
    {
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}