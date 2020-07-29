<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\SystemLimit;

class SystemLimitFactory
{
    public function createFromInspector(string $inspector): SystemLimit
    {
        $object = new SystemLimit();
        $object->setInspector($inspector);

        return $object;
    }
}
