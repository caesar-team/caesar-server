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
     *
     * @throws \Exception
     *
     * @return array<string, array<int, Item>>
     */
    public function share($itemCollectionRequest): array
    {
        $items = [];
        foreach ($itemCollectionRequest->getItems() as $childItem) {
            if (null === $itemCollectionRequest->getOriginalItem()) {
                continue;
            }

            $originalItem = $itemCollectionRequest->getOriginalItem();

            $item = new Item($childItem->getUser());
            $item->setTeam($originalItem->getTeam());
            $directory = $this->getSuggestedDirectory($childItem);
            $item->setParentList($directory);
            $item->setOriginalItem($originalItem);
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($originalItem->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));
            $item->setRelatedItem($originalItem->getRelatedItem());

            try {
                $this->entityManager->persist($item);
                $this->sendItemMessage($item);
                $items[$originalItem->getId()->toString()][] = $item;
                $this->entityManager->flush();
            } catch (\InvalidArgumentException $exception) {
                $this->logger->error(sprintf('Error share item %s, Error: %s', $originalItem->getId()->toString(), $exception->getMessage()));
            }
        }

        return $items;
    }

    /**
     * @param mixed $data
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

        /**
         * @psalm-suppress PossiblyNullReference
         */
        return $childItem->getUser()->getInbox();
    }
}
