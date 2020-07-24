<?php

declare(strict_types=1);

namespace App\Security\Provider;

use App\Model\DTO\SystemUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SystemUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        return new SystemUser();
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        return SystemUser::class === $class;
    }
}
