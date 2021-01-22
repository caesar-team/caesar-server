<?php

declare(strict_types=1);

namespace App\Item;

use App\Entity\Item;

interface ItemDateRefresherInterface
{
    public function refreshDate(Item $item): void;
}
