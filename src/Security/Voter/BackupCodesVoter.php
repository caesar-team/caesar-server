<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BackupCodesVoter extends Voter
{
    public const GET = 'get_backup_codes';

    protected function supports($attribute, $subject)
    {
        return $subject instanceof User
            && self::GET === $attribute
        ;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof User) {
            return false;
        }

        return $subject->isGoogleAuthenticatorEnabled() &&
            User::FLOW_STATUS_FINISHED !== $subject->getFlowStatus()
        ;
    }
}
