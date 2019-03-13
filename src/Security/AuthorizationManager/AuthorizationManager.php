<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class AuthorizationManager
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function findUserByInvitation(string $email): ?UserInterface
    {
        $user = $this->userManager->findUserBy([
            'email' => $email,
            'invitation' => true,
        ]);

        return $user;
    }
}