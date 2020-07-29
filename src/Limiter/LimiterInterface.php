<?php

declare(strict_types=1);

namespace App\Limiter;

use App\Limiter\Model\LimitCheck;

interface LimiterInterface
{
    /**
     * @param LimitCheck[] $checkers
     */
    public function check(array $checkers): void;
}
