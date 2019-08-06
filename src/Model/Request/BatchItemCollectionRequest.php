<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;
use Doctrine\Common\Collections\ArrayCollection;

class BatchItemCollectionRequest
{
    /**
     * @var ChildItem[]|ArrayCollection
     */
    protected $items;

    /**
     * @var string|null
     */
    protected $originalItem;

    public function __construct()
    {
        $this->items = new ArrayCollection();
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

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getOriginalItem(): ?string
    {
        return $this->originalItem;
    }

    public function setOriginalItem(?string $originalItem): void
    {
        $this->originalItem = $originalItem;
    }
}
