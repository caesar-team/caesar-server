<?php

declare(strict_types=1);

namespace App\Utils;

use Exception;

class CircularReferenceHandler
{
    /**
     * @psalm-suppress MissingParamType
     */
    public function __invoke($object)
    {
        try {
            return $object->getId();
        } catch (Exception $exception) {
        }
    }
}
