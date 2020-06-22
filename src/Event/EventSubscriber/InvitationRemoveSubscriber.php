<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Services\InvitationManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

class InvitationRemoveSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $user = $args->getObject();
        if (!$user instanceof User) {
            return;
        }

        InvitationManager::removeInvitation($user, $this->entityManager);
    }
}
