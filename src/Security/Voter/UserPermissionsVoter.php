<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserPermissionsVoter extends Voter
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const AVAILABLE_ATTRIBUTES = [
        self::CREATE,
        self::READ,
        self::UPDATE,
        self::DELETE,
    ];

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::AVAILABLE_ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $user, TokenInterface $token)
    {
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::READ:
                return $this->canRead();
            case self::UPDATE:
                return $this->canUpdate($user);
            case self::DELETE:
                return $this->canDelete($user);
        }


        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(User $user): bool
    {
        return !$user->hasRole(User::ROLE_ANONYMOUS_USER) && !$user->hasRole(User::ROLE_READ_ONLY_USER);
    }

    private function canRead()
    {
        return true;
    }

    private function canUpdate(User $user)
    {
        if ($this->canCreate($user)) {
            return true;
        }
    }

    private function canDelete(User $user)
    {
        if ($this->canCreate($user)) {
            return true;
        }
    }
}