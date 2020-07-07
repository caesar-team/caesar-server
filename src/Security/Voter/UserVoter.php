<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const UPDATE_KEY = 'user_update_keys';

    public const AVAILABLE_ATTRIBUTES = [
        self::UPDATE_KEY,
    ];

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::AVAILABLE_ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof User) {
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

        if (!$subject instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::UPDATE_KEY:
                return $this->canUpdateKey($subject);
        }

        return false;
    }

    private function canUpdateKey(User $user): bool
    {
        return !$user->hasKeys();
    }
}
