<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Model\AwareOwnerAndRelatedItemInterface;
use App\Repository\ItemRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniquePersonalKeypairValidator extends ConstraintValidator
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniquePersonalKeypair) {
            throw new UnexpectedTypeException($constraint, UniquePersonalKeypair::class);
        }

        if (!$value instanceof AwareOwnerAndRelatedItemInterface) {
            return;
        }

        if (null === $value->getOwner() || null === $value->getRelatedItem()) {
            return;
        }

        $item = $this->repository->getPersonalKeyPairByUser($value->getOwner(), $value->getRelatedItem());
        if (null !== $item) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
