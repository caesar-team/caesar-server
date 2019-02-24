<?php

declare(strict_types=1);

namespace App\Utils;


class CircularReferenceHandler
{
    public function __invoke($object)
    {
        try {
            return $object->getId();
        } catch (\Exception $exception) {
        }
    }
}