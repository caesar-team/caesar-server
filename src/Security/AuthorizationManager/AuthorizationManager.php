<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

use App\Entity\Directory;
use App\Entity\Security\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class AuthorizationManager
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(UserManagerInterface $userManager, EntityManagerInterface $entityManager)
    {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
    }

    public function findUserByInvitation(string $email): ?UserInterface
    {
        $user = $this->userManager->findUserByEmail($email);
        if(!$this->hasInvitation($user)) {
            return null;
        }

        if ($user && $this->userHasItems($user)) {
            return null;
        }

        return $user;
    }

    public function hasInvitation(UserInterface $user): bool
    {
        $hash = (InvitationEncoder::initEncoder())->encode($user->getEmail());
        $invitation = $this->entityManager->getRepository(Invitation::class)->findOneBy(['hash' => $hash]);

        if($invitation) {
            return true;
        }

        return false;
    }

    /**
     * @param UserInterface|User $user
     * @return bool
     */
    private function userHasItems(UserInterface $user): bool
    {
        return $this->isEmptyList($user->getInbox()) && $this->isEmptyList($user->getLists()) && $this->isEmptyList($user->getTrash());
    }

    private function isEmptyList(Directory $directory): bool
    {
        return !count($directory->getChildItems());
    }
}