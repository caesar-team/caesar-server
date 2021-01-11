<?php

declare(strict_types=1);

namespace App\Event\Item;

use App\Entity\Item;
use Symfony\Contracts\EventDispatcher\Event;

class ItemsDateRefreshEvent extends Event
{
    /**
     * @var Item[]
     */
    private array $items;

    public function __construct(Item ...$items)
    {
        $this->items = $items;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
