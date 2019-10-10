<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ItemOwnerValidator extends ConstraintValidator
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Collection $value
     * @param Constraint $constraint
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || !is_iterable($value)) {
            return;
        }

        if (!$constraint instanceof ItemOwner) {
            throw new UnexpectedTypeException($constraint, ItemOwner::class);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        foreach ($value as $item) {
            if ($item instanceof Item) {
                $itemUser = $item->getSignedOwner();
                if (null === $user || $user !== $itemUser) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
