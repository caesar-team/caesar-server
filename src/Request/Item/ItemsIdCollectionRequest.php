<?php

declare(strict_types=1);

namespace App\Request\Item;

class ItemsIdCollectionRequest
{
    /**
     * @var string[]
     */
    private array $items = [];

    /**
     * @return string[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
