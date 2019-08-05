<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;
use Doctrine\Common\Collections\ArrayCollection;

class ItemCollectionRequest
{
    /**
     * @var ChildItem[]|ArrayCollection
     */
    protected $items;

    /**
     * @var Item|null
     */
    protected $originalItem;

    public function __construct(Item $item = null)
    {
        $this->originalItem = $item;
        $this->items = new ArrayCollection();
    }

    public function getOriginalItem(): ?Item
    {
        return $this->originalItem;
    }

    /**
     * @return ChildItem[]|ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        return $this->items;
    }

    public function addItem(ChildItem $childItem)
    {
        if (false === $this->items->contains($childItem)) {
            $this->items->add($childItem);
        }
    }

    public function removeItem(ChildItem $childItem)
    {
        $this->items->removeElement($childItem);
    }

    /**
     * @param Item $originalItem
     */
    public function setOriginalItem(?Item $originalItem): void
    {
        $this->originalItem = $originalItem;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
