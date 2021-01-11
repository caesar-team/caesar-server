<?php

declare(strict_types=1);

namespace App\Item;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;

class ItemDateRefresher implements ItemDateRefresherInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function refreshDate(Item $item): void
    {
        $item->refreshLastUpdated();
        $this->entityManager->persist($item);
        foreach ($item->getKeyPairItems() as $keyPairItem) {
            $keyPairItem->refreshLastUpdated();
            $this->entityManager->persist($keyPairItem);
        }

        $relatedItem = $item->getRelatedItem();
        if (null !== $relatedItem) {
            $this->refreshDate($relatedItem);
        }

        $this->entityManager->flush();
    }
}
