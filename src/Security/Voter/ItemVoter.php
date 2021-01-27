<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Directory\UserDirectory;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const CREATE = 'create_list_item';
    public const EDIT = 'edit_list_item';
    public const DELETE = 'delete_list_item';
    public const MOVE = 'move_list_item';
    public const FAVORITE = 'favorite_list_item';

    private const ATTRIBUTES = [
        self::CREATE,
        self::EDIT,
        self::DELETE,
        self::MOVE,
        self::FAVORITE,
    ];

    private AuthorizationCheckerInterface $authorizationChecker;

    private TeamItemVoter $teamItemVoter;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TeamItemVoter $teamItemVoter)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->teamItemVoter = $teamItemVoter;
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

        if ($subject instanceof Item && null !== $subject->getTeam()) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $subject instanceof UserDirectory && $this->canCreate($subject, $user);
            case self::EDIT:
                return $subject instanceof Item && $this->canEdit($subject, $user);
            case self::DELETE:
                return $subject instanceof Item && $this->canDelete($subject, $user);
            case self::MOVE:
                return $subject instanceof Item && $this->canMove($subject, $user);
            case self::FAVORITE:
                return $subject instanceof Item && $this->canFavorite($subject, $user);
        }

        return false;
    }

    private function canMove(Item $item, User $currentUser): bool
    {
        return $currentUser->equals($item->getSignedOwner()) && null === $item->getTeam();
    }

    private function canCreate(UserDirectory $list, User $user): bool
    {
        if ($list->isInbox() && !$list->getUser()->equals($user)) {
            return true;
        }

        return $user->equals($list->getUser()) && !($list->isInbox() || $list->isTrash());
    }

    private function canEdit(Item $item, User $user): bool
    {
        if ($user->equals($item->getSignedOwner())) {
            return true;
        }

        $systemItem = $item->getKeyPairItemByUser($user);
        if (null === $systemItem) {
            return false;
        }

        return AccessEnumType::TYPE_WRITE === $systemItem->getAccess();
    }

    private function canDelete(Item $item, User $user): bool
    {
        if (null !== $item->getRelatedItem()) {
            $item = $item->getRelatedItem();
            if (null !== $item->getTeam()) {
                return $this->authorizationChecker->isGranted(TeamItemVoter::DELETE, $item);
            }

            return $user->equals($item->getSignedOwner());
        }

        return $user->equals($item->getSignedOwner());
    }

    private function canFavorite(Item $item, User $user): bool
    {
        return $user->equals($item->getSignedOwner())
            || null !== $item->getKeyPairItemByUser($user)
        ;
    }
}
