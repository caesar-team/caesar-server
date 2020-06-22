<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamVoter extends Voter
{
    public const TEAM_CREATE = 'team_create';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (self::TEAM_CREATE !== $attribute) {
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

        if (self::TEAM_CREATE !== $attribute) {
            return false;
        }

        if (Team::DEFAULT_GROUP_ALIAS === $subject->getAlias()) {
            return false;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return $user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN);
    }
}
