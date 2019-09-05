<?php

declare(strict_types=1);

namespace App\Strategy\Billing\ProjectUsageRegister;

use App\Context\ProjectUsageRegisterContext;
use App\Entity\Billing\Audit;
use App\Entity\User;
use App\Repository\AuditRepository;

final class UserUsageRegister implements ProjectUsageRegisterInterface
{
    /**
     * @var Audit|null
     */
    private $audit;

    public function __construct(AuditRepository $auditRepository)
    {
        $this->audit = $auditRepository->findOneLatest();
    }

    public function canRegister($object): bool
    {
        $plan = $this->audit->getBillingPlan();

        return 0 <= $plan->getUsersLimit() && $object instanceof User;
    }

    /**
     * @param User $object
     * @param string $actionType
     * @return Audit
     */
    public function register($object, string $actionType): Audit
    {
        if (ProjectUsageRegisterContext::ACTION_UP === $actionType) {
            $this->audit->increaseUsersCount();
        } elseif (ProjectUsageRegisterContext::ACTION_DOWN === $actionType) {
            $this->audit->decreaseUsersCount();
        }

        return $this->audit;
    }
}