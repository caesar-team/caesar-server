<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Throwable;

class EnumTypeValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EnumType) {
            throw new UnexpectedTypeException($constraint, EnumType::class);
        }
        if (empty($constraint->type)) {
            throw new ConstraintDefinitionException('"type" must be specified on constraint EnumType');
        }
        if (!is_string($value)) {
            return;
        }

        try {
            /** @var array $choices */
            $choices = $constraint->type::getValues();
        } catch (Throwable $exception) {
            throw new ConstraintDefinitionException(sprintf('Not found %s class', $constraint->type));
        }

        if (!in_array($value, $choices)) {
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
