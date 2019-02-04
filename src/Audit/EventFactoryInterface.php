<?php

declare(strict_types=1);

namespace App\Audit;

use App\Entity\Audit\AbstractEvent;
use Symfony\Component\HttpFoundation\Request;

interface EventFactoryInterface
{
    /**
     * @param Request $request
     * @param mixed   $target
     *
     * @return AbstractEvent
     */
    public function create(Request $request, $target): AbstractEvent;
}
