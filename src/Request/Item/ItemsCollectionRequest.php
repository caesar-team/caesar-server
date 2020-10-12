<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;

class ItemsCollectionRequest
{
    /**
     * @var Item[]
     */
    private array $items = [];

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
