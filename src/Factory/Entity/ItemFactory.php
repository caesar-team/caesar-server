<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Item;

class ItemFactory
{
    public function create(): Item
    {
        return new Item();
    }
}
