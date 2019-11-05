<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Event\ItemUpdateEvent;
use App\Event\ItemUpdatesFlushEvent;
use App\Repository\ItemUpdateRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ItemUpdater
{

    /**
     * @var ItemUpdateRepository
     */
    private $updateRepository;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ItemUpdateRepository $updateRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->updateRepository = $updateRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createUpdate(Item $item, string $secret, User $currentOwner): void
    {
        $update = $this->extractUpdate($item, $currentOwner);
        $update->setSecret($secret);
        $this->updateRepository->persist($update);
        $this->eventDispatcher->dispatch(new ItemUpdateEvent($item));
        $this->eventDispatcher->dispatch(new ItemUpdatesFlushEvent());
    }

    public function extractUpdate(Item $item, User $user): ItemUpdate
    {
        if ($item->getUpdate()) {
            return $item->getUpdate();
        }

        return new ItemUpdate($item, $user);
    }
}