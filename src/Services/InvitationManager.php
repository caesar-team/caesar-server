<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Security\Invitation;
use App\Entity\User;
use App\Security\AuthorizationManager\InvitationEncoder;
use Doctrine\ORM\EntityManagerInterface;

class InvitationManager
{
    public static function removeInvitation(User $user, EntityManagerInterface $entityManager)
    {
        $hash = (InvitationEncoder::initEncoder())->encode($user->getEmail());
        $invitation = $entityManager->getRepository(Invitation::class)->findOneBy(['hash' => $hash]);
        if ($invitation) {
            $entityManager->remove($invitation);
        }
    }
}