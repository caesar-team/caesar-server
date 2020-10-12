<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Item;

class DirectoryHelper
{
    public static function filterByOffered(Item $item): bool
    {
        return Item::STATUS_OFFERED === $item->getStatus();
    }
}
