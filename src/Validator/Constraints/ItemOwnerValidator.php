<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ItemOwnerValidator extends ConstraintValidator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_iterable($value)) {
            return;
        }

        if (!$constraint instanceof ItemOwner) {
            throw new UnexpectedTypeException($constraint, ItemOwner::class);
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        foreach ($value as $item) {
            if (!$item instanceof Item) {
                continue;
            }

            $itemUser = $item->getSignedOwner();
            if (!$itemUser->equals($user)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation()
                ;
            }
        }
    }
}
