<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\User;
use App\Repository\UserRepository;
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

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

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
            && $subject instanceof Directory
            && ($subject->equals($user->getInbox()) || $subject->equals($user->getTrash()))
        ) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
            case self::EDIT:
                return $subject instanceof Directory && $this->canEdit($subject, $user);
            case self::DELETE:
                return $subject instanceof Directory && $this->canDelete($subject, $user);
            case self::SORT:
                return $subject instanceof Directory && $this->canSort($subject, $user);
            case self::MOVABLE:
                return $subject instanceof Directory && $this->isMovable($subject, $user);
        }

        return false;
    }

    private function canCreate(User $user)
    {
        return !$user->hasRole(User::ROLE_ANONYMOUS_USER);
    }

    private function canEdit(Directory $subject, User $user): bool
    {
        return $this->canSort($subject, $user) && Directory::LIST_DEFAULT !== $subject->getLabel();
    }

    private function canDelete(Directory $subject, User $user): bool
    {
        return $this->canEdit($subject, $user);
    }

    private function canSort(Directory $subject, User $user): bool
    {
        $itemOwner = $this->userRepository->getByList($subject);

        return $user->equals($itemOwner);
    }

    private function isMovable(Directory $subject, User $user): bool
    {
        return $this->canSort($subject, $user);
    }
}
