<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;

class ShareItemCollection
{
    private string $id;

    /**
     * @var Item[]
     */
    private array $items;

    public function __construct(string $id, array $items)
    {
        $this->id = $id;
        $this->items = $items;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
