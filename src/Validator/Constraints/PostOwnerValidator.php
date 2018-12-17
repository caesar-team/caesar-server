<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PostOwnerValidator extends ConstraintValidator
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
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || !is_iterable($value)) {
            return;
        }

        if (!$constraint instanceof PostOwner) {
            throw new UnexpectedTypeException($constraint, PostOwner::class);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $userRepository = $this->entityManager->getRepository(User::class);
        foreach ($value as $post) {
            if ($post instanceof Post) {
                $postUser = $userRepository->getByPost($post);
                if (null === $user || $user !== $postUser) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
