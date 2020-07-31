<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserTeam;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamItemVoter extends Voter
{
    public const CREATE = 'team_create_list_item';
    public const EDIT = 'team_edit_list_item';
    public const DELETE = 'team_delete_list_item';
    public const MOVE = 'team_move_list_item';
    public const FAVORITE = 'team_favorite_list_item';
    public const SHARE = 'team_share_list_item';

    private const ATTRIBUTES = [
        self::CREATE,
        self::EDIT,
        self::DELETE,
        self::MOVE,
        self::FAVORITE,
        self::SHARE,
    ];

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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($subject instanceof Directory && $subject->isTeamTrashDirectory()) {
            return false;
        }

        if ($subject instanceof Directory && null === $subject->getTeam()) {
            return false;
        }

        if ($subject instanceof Item && null === $subject->getTeam()) {
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
            case self::SHARE:
                return $subject instanceof Item && $this->canShare($subject, $user);
            case self::FAVORITE:
                return $subject instanceof Item && $this->canFavorite($subject, $user);
        }

        return false;
    }

    private function canCreate(Directory $subject, User $user): bool
    {
        if (null == $subject->getTeam()) {
            return false;
        }

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            || $userTeam->hasRole(UserTeam::USER_ROLE_MEMBER)
        ;
    }

    private function canEdit(Item $subject, User $user): bool
    {
        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $subject->getOwner()->equals($user)
            || ($subject->getSignedOwner()->equals($user)
                && $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            )
        ;
    }

    private function canDelete(Item $subject, User $user): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $subject->getOwner()->equals($user) || $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN);
    }

    private function canMove(Item $subject, User $user): bool
    {
        return $this->canDelete($subject, $user);
    }

    private function canShare(Item $subject, User $user): bool
    {
        return $this->canDelete($subject, $user);
    }

    private function canFavorite(Item $subject, User $user): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return true;
    }
}
