<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Security\Invitation;
use App\Entity\User;

class InvitationFactory
{
    public function createFromUser(User $user): Invitation
    {
        $invitation = new Invitation();
        $invitation->setHash($user->getHashEmail());

        return $invitation;
    }
}
