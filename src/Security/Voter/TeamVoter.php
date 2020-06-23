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

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Team) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof Team) {
            return false;
        }

        //@todo candidate to refactoring
        if (Team::DEFAULT_GROUP_ALIAS === $subject->getAlias()) {
            return false;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    private function canCreate(Team $team, User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN);
    }

    private function canDelete(Team $team, User $user): bool
    {
        return $this->canCreate($team, $user) && Team::DEFAULT_GROUP_ALIAS !== $team->getAlias();
    }
}
