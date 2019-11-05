<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Directory;
use App\Entity\Item;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\ChildItem;

final class OriginalItemShareFactory extends AbstractShareFactory
{
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
        }
        $this->entityManager->flush();

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