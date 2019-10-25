<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\Request\ChildItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueSharedItemValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param ChildItem $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueSharedItem) {
            throw new UnexpectedTypeException($constraint, UniqueSharedItem::class);
        }

        if (!$value instanceof ChildItem) {
            throw new UnexpectedTypeException($value, ChildItem::class);
        }

        if (!$this->isUnique($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function isUnique(ChildItem $childItem): bool
    {
        if (is_null($childItem->getTeam())) {
            //todo: find an item in personal directories
        }

        //todo: otherwise find an item in team's directories
        return false;
    }
}