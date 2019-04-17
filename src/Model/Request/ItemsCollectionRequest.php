<?php

declare(strict_types=1);

namespace App\Model\Request;


use App\Entity\Item;

class ItemsCollectionRequest
{
    /**
     * @var array|Item[]
     */
    private $items = [];

    /**
     * @return Item[]|array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[]|array $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }
}