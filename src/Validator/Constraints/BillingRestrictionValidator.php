<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Context\BillingRestrictionValidatorContext;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class BillingRestrictionValidator extends ConstraintValidator
{
    /**
     * @var BillingRestrictionValidatorContext
     */
    private $restrictionValidatorContext;

    public function __construct(BillingRestrictionValidatorContext $restrictionValidatorContext)
    {
        $this->restrictionValidatorContext = $restrictionValidatorContext;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BillingRestriction) {
            throw new UnexpectedTypeException($constraint, BillingRestriction::class);
        }

        $violation = $this->restrictionValidatorContext->validate($value);

        if ($violation) {
            $this->context
                ->buildViolation($violation->getMessage())
                ->addViolation()
            ;
        }
    }
}