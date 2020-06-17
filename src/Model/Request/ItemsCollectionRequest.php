<?php

declare(strict_types=1);

namespace App\Model\Request;

class ItemsCollectionRequest
{
    /**
     * @var string[]
     */
    private $items = [];

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string[] $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }
}
