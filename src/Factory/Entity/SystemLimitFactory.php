<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\SystemLimit;
use App\Limiter\Inspector\InspectorInterface;

class SystemLimitFactory
{
    public function createFromInspector(InspectorInterface $inspector): SystemLimit
    {
        $object = new SystemLimit();
        $object->setInspector(get_class($inspector));

        return $object;
    }
}
