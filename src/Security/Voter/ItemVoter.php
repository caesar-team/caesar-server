<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const CREATE = 'create_list_item';
    public const EDIT = 'edit_list_item';
    public const DELETE = 'delete_list_item';
    public const MOVE = 'move_list_item';
    public const FAVORITE = 'favorite_list_item';
    public const SHARE = 'share_list_item';

    private const ATTRIBUTES = [
        self::CREATE,
        self::EDIT,
        self::DELETE,
        self::MOVE,
        self::FAVORITE,
        self::SHARE,
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
        if (!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $subject instanceof Directory && $this->canCreate($subject, $user);
            case self::EDIT:
                return $subject instanceof Item && $this->canEdit($subject, $user);
            case self::DELETE:
                return $subject instanceof Item && $this->canDelete($subject, $user);
            case self::MOVE:
                return $subject instanceof Item && $this->canMove($subject, $user);
            case self::FAVORITE:
                return $subject instanceof Item && $this->canFavorite($subject, $user);
            case self::SHARE:
                return $subject instanceof Item && $this->canShare($subject, $user);
        }

        return false;
    }

    private function canMove(Item $item, User $currentUser): bool
    {
        return $currentUser->equals($item->getSignedOwner());
    }

    private function canCreate(Directory $list, User $user): bool
    {
        $itemOwner = $this->userRepository->getByList($list);

        return $user->equals($itemOwner)
            && !($list->equals($user->getInbox()) || $list->equals($user->getTrash()))
        ;
    }

    private function canEdit(Item $item, User $user): bool
    {
        return $user->equals($item->getOwner()) || $user->equals($item->getSignedOwner());
    }

    private function canDelete(Item $item, User $user): bool
    {
        return $user->equals($item->getSignedOwner());
    }

    private function canFavorite(Item $item, User $user): bool
    {
        return $user->equals($item->getSignedOwner());
    }

    private function canShare(Item $item, User $user): bool
    {
        return $user->equals($item->getSignedOwner());
    }
}
