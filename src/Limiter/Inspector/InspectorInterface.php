<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use App\Entity\SystemLimit;

interface InspectorInterface
{
    public function getUsed(int $addedSize = 0): int;

    public function inspect(SystemLimit $limit, int $addedSize): void;
}
