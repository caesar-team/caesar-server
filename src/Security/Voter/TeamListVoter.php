<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
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

    private UserRepository $userRepository;

    private TeamRepository $teamRepository;

    public function __construct(
        UserRepository $userRepository,
        TeamRepository $teamRepository
    ) {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (self::MOVABLE !== $attribute && $subject instanceof Directory
            && ($subject->isTeamTrashDirectory() || $subject->isTeamDefaultDirectory())
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

    private function canEdit(Directory $subject, User $user): bool
    {
        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN);
    }

    private function canDelete(Directory $subject, User $user): bool
    {
        return $this->canEdit($subject, $user);
    }

    private function canSort(Directory $subject, User $user): bool
    {
        return $this->canEdit($subject, $user);
    }

    private function isMovable(Directory $subject, User $user): bool
    {
        if (null === $subject->getTeam()) {
            return false;
        }

        $userTeam = $subject->getTeam()->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        return $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)
            || $userTeam->hasRole(UserTeam::USER_ROLE_MEMBER)
        ;
    }
}
