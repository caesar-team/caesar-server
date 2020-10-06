<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Repository\ItemRepository;
use App\Team\AwareOwnerAndTeamInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueTeamKeypairValidator extends ConstraintValidator
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueTeamKeypair) {
            throw new UnexpectedTypeException($constraint, UniqueTeamKeypair::class);
        }

        if (!$value instanceof AwareOwnerAndTeamInterface) {
            return;
        }

        if (null === $value->getOwner() || null === $value->getTeam()) {
            return;
        }

        $item = $this->repository->getTeamKeyPairByUser($value->getOwner(), $value->getTeam());
        if (null !== $item) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
