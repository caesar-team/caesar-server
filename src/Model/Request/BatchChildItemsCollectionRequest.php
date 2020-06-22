<?php

declare(strict_types=1);

namespace App\Model\Request;

use Doctrine\Common\Collections\ArrayCollection;

class BatchChildItemsCollectionRequest
{
    /**
     * @var ItemCollectionRequest[]|ArrayCollection
     */
    private $collectionItems;

    public function __construct()
    {
        $this->collectionItems = new ArrayCollection();
    }

    /**
     * @return ItemCollectionRequest[]|ArrayCollection
     */
    public function getCollectionItems(): ArrayCollection
    {
        return $this->collectionItems;
    }

    public function addChildItemCollectionRequest(ItemCollectionRequest $itemCollectionRequest): void
    {
        if (false === $this->collectionItems->contains($itemCollectionRequest)) {
            $this->collectionItems->add($itemCollectionRequest);
        }
    }
}
