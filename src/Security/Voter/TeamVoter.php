<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamVoter extends Voter
{
    public const DELETE = 'team_delete';
    public const CREATE = 'team_create';
    public const EDIT = 'team_edit';
    public const PINNED = 'team_pinned';
    public const GET_KEYPAIR = 'get_keypair';
    public const LEAVE = 'team_leave';

    private const AVAILABLE_ATTRIBUTES = [
        self::CREATE, self::DELETE, self::EDIT, self::PINNED, self::GET_KEYPAIR, self::LEAVE,
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

        switch ($attribute) {
            case self::CREATE:
                return $subject instanceof User && $this->canCreate($subject);
            case self::DELETE:
                return $subject instanceof Team && $this->canDelete($subject, $user);
            case self::EDIT:
                return $subject instanceof Team && $this->canEdit($subject, $user);
            case self::PINNED:
                return $subject instanceof Team && $this->canPinned($subject, $user);
            case self::GET_KEYPAIR:
                return $subject instanceof Team && $this->canGetKeypair($subject, $user);
            case self::LEAVE:
                return $subject instanceof Team && $this->canLeave($subject, $user);
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_MANAGER);
    }

    private function canEdit(Team $team, User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN)
            && Team::DEFAULT_GROUP_ALIAS !== $team->getAlias()
        ;
    }

    private function canDelete(Team $team, User $user): bool
    {
        return $this->canEdit($team, $user);
    }

    private function canPinned(Team $team, User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    private function canGetKeypair(Team $team, User $user): bool
    {
        return null !== $team->getUserTeamByUser($user);
    }

    private function canLeave(Team $team, User $user): bool
    {
        return null !== $team->getUserTeamByUser($user);
    }
}
