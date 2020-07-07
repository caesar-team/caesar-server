<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserTeam;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserTeamVoter extends Voter
{
    public const INVITE = 'user_team_invite';
    public const EDIT = 'user_team_edit';
    public const VIEW = 'user_team_view';
    public const REMOVE = 'user_team_remove_member';

    private const ROLES_TO_VIEW = [
        UserTeam::USER_ROLE_ADMIN,
        UserTeam::USER_ROLE_MEMBER,
    ];

    public const AVAILABLE_ATTRIBUTES = [
        self::INVITE,
        self::EDIT,
        self::VIEW,
        self::REMOVE,
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

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        if (!$subject instanceof UserTeam) {
            return false;
        }

        switch ($attribute) {
            case self::INVITE:
                return $this->canInvite($subject, $user);
            case self::REMOVE:
                return $this->canRemove($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
        }

        return false;
    }

    private function canEdit(UserTeam $userTeam, User $user): bool
    {
        return  $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            || $user->hasRole(User::ROLE_ADMIN)
        ;
    }

    private function canRemove(UserTeam $userTeam, User $user): bool
    {
        return $this->canEdit($userTeam, $user);
    }

    private function canView(UserTeam $userTeam, User $user): bool
    {
        return in_array($userTeam->getUserRole(), self::ROLES_TO_VIEW)
            || $user->hasRole(User::ROLE_ADMIN)
        ;
    }

    private function canInvite(UserTeam $userTeam, User $user): bool
    {
        return $userTeam->hasRole(UserTeam::USER_ROLE_MEMBER);
    }
}
