<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
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
        $userRepository = $this->entityManager->getRepository(User::class);
        foreach ($value as $item) {
            if ($item instanceof Item) {
                $itemUser = $userRepository->getByItem($item);
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
