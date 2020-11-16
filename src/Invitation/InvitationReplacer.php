<?php

declare(strict_types=1);

namespace App\Invitation;

use App\Entity\Security\Invitation;
use App\Entity\User;
use App\Factory\Entity\InvitationFactory;
use App\Repository\InvitationRepository;

class InvitationReplacer
{
    private InvitationFactory $factory;

    private InvitationRepository $repository;

    public function __construct(InvitationRepository $repository, InvitationFactory $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function replaceByUser(User $user): Invitation
    {
        $invitation = $this->factory->createFromUser($user);
        $this->repository->deleteByHash($invitation->getHash());

        return $invitation;
    }
}
