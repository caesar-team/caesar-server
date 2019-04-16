<?php

declare(strict_types=1);

namespace App\Model\Request;


use App\Entity\Item;
use App\Entity\ItemUpdate;

class EditItemRequest
{
    /**
     * @var ItemUpdateRequest|null
     */
    private $originalItem;

    /**
     * @var Item
     */
    private $item;

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @return ItemUpdateRequest|null
     */
    public function getOriginalItem(): ?ItemUpdateRequest
    {
        return $this->originalItem;
    }

    /**
     * @param ItemUpdateRequest|null $originalItem
     */
    public function setOriginalItem(?ItemUpdateRequest $originalItem): void
    {
        $this->originalItem = $originalItem;
    }
}