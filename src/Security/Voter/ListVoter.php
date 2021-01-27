<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\UserDirectory;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ListVoter extends Voter
{
    public const CREATE = 'create_list';
    public const EDIT = 'edit_list';
    public const SORT = 'sort_list';
    public const DELETE = 'delete_list';
    public const MOVABLE = 'movable_list';

    public const AVAILABLE_ATTRIBUTES = [
        self::CREATE, self::EDIT, self::DELETE, self::SORT, self::MOVABLE,
    ];

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::AVAILABLE_ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (self::MOVABLE !== $attribute
            && $subject instanceof UserDirectory
            && ($subject->isInbox() || $subject->isTrash())
        ) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::EDIT:
                return $subject instanceof UserDirectory && $this->canEdit($subject, $user);
            case self::DELETE:
                return $subject instanceof UserDirectory && $this->canDelete($subject, $user);
            case self::SORT:
                return $subject instanceof UserDirectory && $this->canSort($subject, $user);
            case self::MOVABLE:
                return $subject instanceof UserDirectory && $this->isMovable($subject, $user);
        }

        return false;
    }

    private function canCreate(User $user)
    {
        return !$user->hasRole(User::ROLE_ANONYMOUS_USER);
    }

    private function canEdit(UserDirectory $subject, User $user): bool
    {
        return $this->canSort($subject, $user) && Directory::LIST_DEFAULT !== $subject->getLabel();
    }

    private function canDelete(UserDirectory $subject, User $user): bool
    {
        return $this->canSort($subject, $user) && !$subject->isDefault();
    }

    private function canSort(UserDirectory $subject, User $user): bool
    {
        return $user->equals($subject->getUser());
    }

    private function isMovable(UserDirectory $subject, User $user): bool
    {
        return $this->canSort($subject, $user);
    }
}
