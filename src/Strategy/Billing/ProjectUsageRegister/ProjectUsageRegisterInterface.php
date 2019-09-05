<?php

declare(strict_types=1);


namespace App\Strategy\Billing\ProjectUsageRegister;


use App\Entity\Billing\Audit;

interface ProjectUsageRegisterInterface
{
    public function canRegister($object): bool;

    public function register($object, string $actionType): Audit;
}