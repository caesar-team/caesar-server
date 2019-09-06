<?php

declare(strict_types=1);

namespace App\Context;

use App\Model\DTO\BillingViolation;
use App\Strategy\Billing\Validator\BillingRestrictionValidatorInterface;

final class BillingRestrictionValidatorContext
{

    /**
     * @var BillingRestrictionValidatorInterface[]
     */
    private $restrictionValidators;

    public function __construct(BillingRestrictionValidatorInterface ...$restrictionValidators)
    {
        $this->restrictionValidators = $restrictionValidators;
    }

    public function validate($value): ?BillingViolation
    {
        foreach ($this->restrictionValidators as $restrictionValidator) {
            if (!$restrictionValidator->canValidate($value)) {
                continue;
            }

            return $restrictionValidator->validate($value);
        }

        return null;
    }
}