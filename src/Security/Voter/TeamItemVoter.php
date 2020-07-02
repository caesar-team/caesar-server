<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\User;
use App\Entity\UserTeam;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamItemVoter extends Voter
{
    public const CREATE = 'create_team_list_item';

    private const ATTRIBUTES = [
        self::CREATE,
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

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        switch ($attribute) {
            case self::CREATE:
                return $subject instanceof Directory && $this->canCreate($subject, $user);
        }

        return false;
    }

    private function canCreate(Directory $subject, User $user): bool
    {
        if (null == $subject->getTeam()) {
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
