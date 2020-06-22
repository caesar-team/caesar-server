<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;

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

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getOriginalItem(): ?ItemUpdateRequest
    {
        return $this->originalItem;
    }

    public function setOriginalItem(?ItemUpdateRequest $originalItem): void
    {
        $this->originalItem = $originalItem;
    }
}
