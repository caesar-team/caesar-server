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

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE, self::DELETE, self::EDIT])) {
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
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
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
}
