<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\Item;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ItemRevokeSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $item = $args->getObject();
        if (!$item instanceof Item) {
            return;
        }
        if (!$this->isChild($item)) {
            return;
        }

        $this->removeAbandonedUser($item);
    }

    /**
     * @param Item $item
     */
    private function removeAbandonedUser(Item $item)
    {
        $user = $item->getSignedOwner();
        if (!$user->isFullUser()) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }

    private function isChild(Item $item): bool
    {
        return !is_null($item->getOriginalItem());
    }
}