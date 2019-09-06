<?php

declare(strict_types=1);

namespace App\Strategy\Billing\Validator;

use App\Model\DTO\BillingViolation;

interface BillingRestrictionValidatorInterface
{
    public function canValidate($value): bool;
    public function validate($value): ?BillingViolation;
}