<?php

declare(strict_types=1);

namespace App\Context;

use App\Strategy\Billing\ProjectUsageRegister\ProjectUsageRegisterInterface;

final class ProjectUsageRegisterContext
{
    public const ACTION_UP = 'up';
    public const ACTION_DOWN = 'down';

    /**
     * @var ProjectUsageRegisterInterface[]
     */
    private $usageRegisters;

    public function __construct(ProjectUsageRegisterInterface ...$usageRegisters)
    {
        $this->usageRegisters = $usageRegisters;
    }

    public function registerUsage($object, string $actionType): ?ProjectUsageRegisterInterface
    {

        foreach ($this->usageRegisters as $usageRegister) {
            if (!$usageRegister->canRegister($object)) {
                continue;
            }
            $usageRegister->register($object, $actionType);

            return $usageRegister;
        }

        return null;
    }
}