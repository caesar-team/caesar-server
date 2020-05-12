<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Throwable;

/**
 * class EnumTypeValidator.
 */
class EnumTypeValidator extends ConstraintValidator
{
    /**
     * @param Collection $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($constraint->type)) {
            throw new ConstraintDefinitionException('"type" must be specified on constraint EnumType');
        }

        try {
            $choices = $constraint->type::getValues();
        } catch (Throwable $exception) {
            throw new ConstraintDefinitionException(sprintf('Not found %s class', $constraint->type));
        }

        if (null !== $value && !in_array($value, $choices)) {
            $variants = implode(', ', $choices);
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('%current_type%', $value)
                ->setParameter('%variants%', $variants)
                ->addViolation();
        }
    }
}
