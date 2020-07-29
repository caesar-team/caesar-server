<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use App\Entity\SystemLimit;
use App\Limiter\Exception\RestrictedException;

abstract class AbstractInspector implements InspectorInterface
{
    abstract public function getErrorMessage(): string;

    public function inspect(SystemLimit $limit, int $addedSize): void
    {
        $used = $this->getUsed($addedSize);
        if (!$limit->isRestricted($used)) {
            return;
        }

        throw new RestrictedException($this->getErrorMessage());
    }
}
