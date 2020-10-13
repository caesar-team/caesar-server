<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

final class RemoveDirectoriesDeletedTeamSubscriber implements EventSubscriberInterface
{
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
        $team = $args->getObject();

        if (!$team instanceof Team) {
            return;
        }

        $args->getObjectManager()->remove($team->getLists());
        $args->getObjectManager()->remove($team->getTrash());
    }
}
