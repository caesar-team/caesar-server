<?php

declare(strict_types=1);

namespace App\Security\Voter;


use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamVoter extends Voter
{
    const TEAM_CREATE = 'create';
    const TEAM_EDIT   = 'edit';
    const TEAM_VIEW   = 'view';
    const TEAM_ADD_MEMBER = 'add_member';
    const TEAM_REMOVE_MEMBER = 'remove_member';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::TEAM_CREATE, self::TEAM_EDIT, self::TEAM_VIEW])) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param User $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (in_array($attribute, [self::TEAM_CREATE, self::TEAM_EDIT, self::TEAM_VIEW, self::TEAM_ADD_MEMBER])) {

            return false;
        }

        throw new \LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }
}