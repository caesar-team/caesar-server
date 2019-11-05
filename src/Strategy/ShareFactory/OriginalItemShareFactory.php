<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Directory;
use App\Entity\Item;
use App\Event\ShareEvent;
use App\Event\SharesFlushEvent;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\ChildItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class OriginalItemShareFactory extends AbstractShareFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($entityManager);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param BatchItemCollectionRequest $itemCollectionRequest
     * @return array|Item[]
     * @throws \Exception
     */
    public function share($itemCollectionRequest): array
    {
        $items = [];
        foreach ($itemCollectionRequest->getItems() as $childItem) {
            $item = new Item($childItem->getUser());
            $item->setTeam($childItem->getTeam());
            $directory = $this->getSuggestedDirectory($childItem);
            $item->setParentList($directory);
            $item->setOriginalItem($itemCollectionRequest->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($itemCollectionRequest->getOriginalItem()->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));

            $this->entityManager->persist($item);
            $items[$itemCollectionRequest->getOriginalItem()->getId()->toString()][] = $item;
            $this->eventDispatcher->dispatch(new ShareEvent($item));
        }
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new SharesFlushEvent());

        return $items;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function canShare($data): bool
    {
        return $data instanceof BatchItemCollectionRequest;
    }

    private function getSuggestedDirectory(ChildItem $childItem): Directory
    {
        if ($childItem->getTeam()) {
            return $childItem->getTeam()->getDefaultDirectory();
        }

        return $childItem->getUser()->getInbox();
    }
}