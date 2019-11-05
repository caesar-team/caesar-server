<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Item;
use Symfony\Contracts\EventDispatcher\Event;

final class ShareEvent extends Event
{
    /**
     * @var Item
     */
    private $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}