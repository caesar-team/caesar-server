<?php

declare(strict_types=1);

namespace App\Strategy\Billing\Validator;

use App\Entity\Billing\Plan;
use App\Entity\User;
use App\Model\DTO\BillingViolation;
use App\Services\Billing\BillingHelper;
use Doctrine\ORM\NonUniqueResultException;

final class UserRestrictionValidator implements BillingRestrictionValidatorInterface
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
        return $value instanceof User;
    }

    /**
     * @param User $value
     * @return BillingViolation|null
     * @throws NonUniqueResultException
     */
    public function validate($value): ?BillingViolation
    {
        if (!$this->billingHelper->hasRestriction(Plan::FIELD_USERS_LIMIT)) {
            return null;
        }

        if (0 >= $this->billingHelper->getRemains()->remainingUsers) {
            return new BillingViolation('Users limit reached.');
        }

        return null;
    }
}