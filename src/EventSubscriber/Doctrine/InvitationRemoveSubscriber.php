<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\Entity\Security\Invitation;
use App\Entity\User;
use App\Repository\InvitationRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

class InvitationRemoveSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

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

        /** @var InvitationRepository $repository */
        $repository = $this->entityManager->getRepository(Invitation::class);
        $repository->deleteByHash($user->getHashEmail());
    }
}
