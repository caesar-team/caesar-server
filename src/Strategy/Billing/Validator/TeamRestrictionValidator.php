<?php

declare(strict_types=1);

namespace App\Strategy\Billing\Validator;

use App\Entity\Billing\Plan;
use App\Entity\Team;
use App\Model\DTO\BillingViolation;
use App\Services\Billing\BillingHelper;
use Doctrine\ORM\NonUniqueResultException;

final class TeamRestrictionValidator implements BillingRestrictionValidatorInterface
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
        return $value instanceof Team;
    }

    /**
     * @param Team $value
     * @return BillingViolation|null
     * @throws NonUniqueResultException
     */
    public function validate($value): ?BillingViolation
    {
        if (!$this->billingHelper->hasRestriction(Plan::FIELD_TEAMS_LIMIT)) {
            return null;
        }

        if (0 >= $this->billingHelper->getRemains()->remainingTeams) {
            return new BillingViolation('Teams limit reached.');
        }

        return null;
    }
}