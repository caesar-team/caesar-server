<?php

declare(strict_types=1);

namespace App\Strategy\Billing\Validator;

use App\Entity\Billing\Plan;
use App\Entity\Item;
use App\Model\DTO\BillingViolation;
use App\Services\Billing\BillingHelper;

final class ItemRestrictionValidator implements BillingRestrictionValidatorInterface
{
    /**
     * @var BillingHelper
     */
    private $billingHelper;

    public function __construct(BillingHelper $billingHelper)
    {
        $this->billingHelper = $billingHelper;
    }

    public function canValidate($value): bool
    {
        return $value instanceof Item;
    }

    /**
     * @param Item $value
     * @return BillingViolation|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($value): ?BillingViolation
    {
        if ($this->billingHelper->hasRestriction(Plan::FIELD_ITEMS_LIMIT) && 0 >= $this->billingHelper->getRemains()->remainingItems) {
            return new BillingViolation('Items limit reached.');
        }

        if ($this->billingHelper->hasRestriction(Plan::FIELD_MEMORY_LIMIT) && strlen($value->getSecret()) > $this->billingHelper->getRemains()->remainingMemory) {
            return new BillingViolation('Memory limit reached.');
        }

        return null;
    }
}