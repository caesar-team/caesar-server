<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

final class ShareItemSubscriber implements EventSubscriber
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $item = $args->getObject();
        if (!$this->canProcess($item)) {
            return;
        }

        if (!$this->isUnique($item)) {
            throw new \InvalidArgumentException('item.invite.user.already_invited');
        }

    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $item = $args->getObject();
        if (!$this->canProcess($item)) {
            return;
        }
    }

    private function canProcess($item): bool
    {
        if (!$item instanceof Item) {
            return false;
        }

        if (!$item->getOriginalItem()) {
            return false;
        }

        return true;
    }

    private function isUnique(Item $item): bool
    {
        $originalItem = $item->getOriginalItem();
        $handledItems = array_filter($originalItem->getSharedItems()->toArray(), function (Item $sharedItem) use ($item) {
            $owner = $item->getSignedOwner();
            if ($sharedItem->getTeam() === $item->getTeam()) {
                return $sharedItem->getSignedOwner() === $owner;
            }

            return false;
        });

        return 0 === count($handledItems);
    }
}