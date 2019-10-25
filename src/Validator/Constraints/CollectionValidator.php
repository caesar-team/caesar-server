<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

final class CollectionValidator extends ConstraintValidator
{
    public function validate($collection, Constraint $constraint)
    {
        if (!$constraint instanceof Collection) {
            throw new UnexpectedTypeException($constraint, Collection::class);
        }

        if (!is_array($collection)) {
            throw new UnexpectedTypeException($collection, 'array');
        }

        $validator = Validation::createValidator();

        foreach ($collection as $item) {
            $errors = $validator->validate($item, $constraint->getItemConstraint());

            if (0 < count($errors)) {
                $this->context->buildViolation($errors[0]->getMessage())
                    ->addViolation();
            }
        }
    }
}