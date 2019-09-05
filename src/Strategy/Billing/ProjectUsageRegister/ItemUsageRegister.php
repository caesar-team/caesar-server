<?php

declare(strict_types=1);

namespace App\Strategy\Billing\ProjectUsageRegister;

use App\Context\ProjectUsageRegisterContext;
use App\Entity\Billing\Audit;
use App\Entity\Item;
use App\Repository\AuditRepository;

final class ItemUsageRegister implements ProjectUsageRegisterInterface
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

        return 0 <= $plan->getItemsLimit() && $object instanceof Item;
    }

    /**
     * @param Item $object
     * @param $actionType
     *
     * @return Audit
     */
    public function register($object, string $actionType): Audit
    {
        if (ProjectUsageRegisterContext::ACTION_UP === $actionType) {
            $this->audit->increaseItemsCount();
            $this->audit->increaseMemoryUsage(strlen($object->getSecret()));

        } elseif (ProjectUsageRegisterContext::ACTION_DOWN === $actionType) {
            $this->audit->decreaseItemsCount();
            $this->audit->decreaseMemoryUsage(strlen($object->getSecret()));
        }

        return $this->audit;
    }
}