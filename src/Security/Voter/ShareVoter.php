<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Share;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ShareVoter extends Voter
{
    public const EDIT_SHARE = 'edit_share';
    public const DELETE_SHARE = 'delete_share';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Share && in_array($attribute, [self::EDIT_SHARE, self::DELETE_SHARE], true);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        switch ($attribute) {
            case self::DELETE_SHARE:
            case self::EDIT_SHARE:
                return $subject instanceof Share && $this->canEdit($subject, $user);
        }

        return false;
    }

    private function canEdit(Share $share, UserInterface $user): bool
    {
        return $share->getOwner() === $user;
    }
}
