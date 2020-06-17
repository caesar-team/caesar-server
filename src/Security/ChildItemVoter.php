<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Item;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChildItemVoter extends Voter
{
    public const REVOKE_CHILD_ITEM = 'revoke_child_item';
    public const CHANGE_ACCESS = 'change_access_child_item';
    public const UPDATE_CHILD_ITEM = 'update_child_item';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::REVOKE_CHILD_ITEM, self::CHANGE_ACCESS, self::UPDATE_CHILD_ITEM])) {
            return false;
        }

        if (!$subject instanceof Item) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Item   $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //todo: rework this validation by new work flow
        return true;
    }
}
