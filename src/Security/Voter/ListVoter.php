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
    public const EDIT = 'edit_list';
    public const SORT = 'sort_list';
    public const DELETE = 'delete_list';
    public const CREATE_ITEM = 'create_item_list';

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
        if (!in_array($attribute, [self::EDIT, self::DELETE, self::SORT, self::CREATE_ITEM])) {
            return false;
        }

        if (!$subject instanceof Directory) {
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
        if (!$subject instanceof Directory) {
            return false;
        }
        if ($subject->equals($user->getInbox()) || $subject->equals($user->getTrash())) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::SORT:
                return $this->canSort($subject, $user);
            case self::CREATE_ITEM:
                return $this->canCreateItem($subject, $user);
        }

        return false;
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

    private function canCreateItem(Directory $subject, User $user): bool
    {
        return $this->canSort($subject, $user);
    }
}
