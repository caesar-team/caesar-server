<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\User;
use App\Event\ItemUpdateEvent;
use App\Event\ItemUpdatesFlushEvent;
use App\Model\Request\ItemCollectionRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChildItemActualizer
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @var ItemUpdater
     */
    private $itemUpdater;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * InviteHandler constructor.
     * @param ItemUpdater $itemUpdater
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ItemUpdater $itemUpdater,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->itemUpdater = $itemUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ItemCollectionRequest $request
     * @param User $currentOwner
     */
    public function updateCollection(ItemCollectionRequest $request, User $currentOwner): void
    {
        $parentItem = $request->getOriginalItem();
        if (null !== $parentItem->getOriginalItem()) {
            $parentItem = $parentItem->getOriginalItem();
        }

        foreach ($request->getItems() as $childItem) {
            /** @var Item $item */
            /** @var User $user */
            [$item, $user] = $this->getItem($childItem->getUser(), $parentItem);

            if ($currentOwner === $user || Item::CAUSE_SHARE === $item->getCause()) {
                $item->setSecret($childItem->getSecret());
            } else {
                $update = $this->itemUpdater->extractUpdate($item, $currentOwner);
                $update->setSecret($childItem->getSecret());
            }
            if ($childItem->getLink()) {
                $item->setLink($childItem->getLink());
            }

            $this->entityManager->persist($item);
            $this->eventDispatcher->dispatch(new ItemUpdateEvent($item));
        }

        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new ItemUpdatesFlushEvent());
    }

    /**
     * @param User $user
     * @param Item $originalItem
     * @return array
     */
    private function getItem(User $user, Item $originalItem): array
    {
        $owner = $originalItem->getSignedOwner();
        if ($user === $owner) {
            return [$originalItem, $user];
        }

        foreach ($originalItem->getSharedItems() as $sharedItem) {
            $owner = $sharedItem->getSignedOwner();
            if ($user === $owner) {
                return [$sharedItem, $user];
            }
        }

        throw new \LogicException('No Such user in original invite '.$user->getId()->toString());
    }
}
