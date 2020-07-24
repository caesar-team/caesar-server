<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class SystemUser implements UserInterface
{
    public function getRoles()
    {
        return [User::ROLE_SYSTEM_USER, User::ROLE_USER];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return 'system-user';
    }

    public function eraseCredentials()
    {
    }
}
