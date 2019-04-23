<?php

declare(strict_types=1);

namespace App\Security\Voter;


use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    const GROUP_CREATE = 'create';
    const GROUP_EDIT   = 'edit';
    const GROUP_VIEW   = 'view';
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
        if (!in_array($attribute, [self::GROUP_CREATE, self::GROUP_EDIT, self::GROUP_VIEW])) {
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
     * @param Group $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (in_array($attribute, [self::GROUP_CREATE, self::GROUP_EDIT, self::GROUP_VIEW])) {

            return false;
        }

        throw new \LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }
}