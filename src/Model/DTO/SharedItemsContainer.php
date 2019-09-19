<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;

class SharedItemsContainer
{

    /**
     * @var array|Item[]
     */
    private $items = [];

    /**
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }
}