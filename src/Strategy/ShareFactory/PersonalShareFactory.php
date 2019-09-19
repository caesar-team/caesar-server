<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;
use App\Model\Request\BatchItemCollectionRequest;

final class PersonalShareFactory extends AbstractShareFactory
{
    /**
     * @param BatchItemCollectionRequest $personal
     * @return array|Item[]
     * @throws \Exception
     */
    public function share($personal): array
    {
        $items = [];
        foreach ($personal->getItems() as $childItem) {
            $item = new Item($childItem->getUser());
            $directory = $childItem->getUser()->getInbox();
            $item->setParentList($directory);
            $item->setOriginalItem($personal->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($personal->getOriginalItem()->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));

            $this->entityManager->persist($item);
            $this->sendItemMessage($childItem);
            $items[$personal->getOriginalItem()->getId()->toString()][] = $item;
        }

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
}