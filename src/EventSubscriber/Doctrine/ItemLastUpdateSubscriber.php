<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

class ItemLastUpdateSubscriber implements EventSubscriberInterface
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
            Events::postUpdate,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();
        if (!$item instanceof Item) {
            return;
        }

        if (empty($item->getKeyPairItems())) {
            return;
        }

        foreach ($item->getKeyPairItems() as $keyPairItem) {
            $keyPairItem->refreshLastUpdated();
            $this->entityManager->persist($keyPairItem);
        }

        $this->entityManager->flush();
    }
}
