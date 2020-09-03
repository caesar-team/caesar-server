<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserPermissionsVoter extends Voter
{
    public const CREATE = 'user_permission_create';
    public const READ = 'user_permission_read';
    public const UPDATE = 'user_permission_update';
    public const DELETE = 'user_permission_delete';
    public const AVAILABLE_ATTRIBUTES = [
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

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject);
            case self::READ:
                return $this->canRead();
            case self::UPDATE:
                return $this->canUpdate($subject);
            case self::DELETE:
                return $this->canDelete($subject);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canCreate(User $user): bool
    {
        return !$user->hasRole(User::ROLE_ANONYMOUS_USER) && !$user->hasRole(User::ROLE_READ_ONLY_USER);
    }

    private function canRead(): bool
    {
        return true;
    }

    private function canUpdate(User $user): bool
    {
        if ($this->canCreate($user)) {
            return true;
        }

        return false;
    }

    private function canDelete(User $user): bool
    {
        if ($this->canCreate($user)) {
            return true;
        }

        return false;
    }
}
