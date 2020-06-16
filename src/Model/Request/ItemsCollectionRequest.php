<?php

declare(strict_types=1);

namespace App\Model\Request;

class ItemsCollectionRequest
{
    /**
     * @var array
     */
    private $items = [];

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }
}
