<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\TeamDirectory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamListVoter extends Voter
{
    public const SHOW = 'team_show_list';
    public const CREATE = 'team_create_list';
    public const EDIT = 'team_edit_list';
    public const DELETE = 'team_delete_list';
    public const SORT = 'team_sort_list';
    public const MOVABLE = 'team_movable_list';

    private const ATTRIBUTES = [
        self::SHOW,
        self::CREATE,
        self::EDIT,
        self::DELETE,
        self::SORT,
        self::MOVABLE,
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

        if (self::MOVABLE !== $attribute
            && $subject instanceof TeamDirectory
            && $subject->isTrash()
        ) {
            return false;
        }

        if (!in_array($attribute, [self::MOVABLE, self::SORT, self::EDIT])
            && $subject instanceof TeamDirectory
            && $subject->isDefault()
        ) {
            return false;
        }

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        switch ($attribute) {
            case self::SHOW:
                return $subject instanceof Team && $this->canShow($subject, $user);
            case self::CREATE:
                return $subject instanceof Team && $this->canCreate($subject, $user);
            case self::EDIT:
                return $subject instanceof TeamDirectory && $this->canEdit($subject, $user);
            case self::DELETE:
                return $subject instanceof TeamDirectory && $this->canDelete($subject, $user);
            case self::SORT:
                return $subject instanceof TeamDirectory && $this->canSort($subject, $user);
            case self::MOVABLE:
                return $subject instanceof TeamDirectory && $this->isMovable($subject, $user);
        }

        return false;
    }

    private function canShow(Team $subject, User $user): bool
    {
        $userTeam = $subject->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            || $userTeam->hasRole(UserTeam::USER_ROLE_MEMBER)
        ;
    }

    private function canCreate(Team $subject, User $user): bool
    {
        $userTeam = $subject->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN);
    }

    private function canEdit(TeamDirectory $subject, User $user): bool
    {
        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN) && Directory::LIST_DEFAULT !== $subject->getLabel();
    }

    private function canDelete(TeamDirectory $subject, User $user): bool
    {
        return $this->canEdit($subject, $user);
    }

    private function canSort(TeamDirectory $subject, User $user): bool
    {
        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN);
    }

    private function isMovable(TeamDirectory $subject, User $user): bool
    {
        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            || $userTeam->hasRole(UserTeam::USER_ROLE_MEMBER)
        ;
    }
}
