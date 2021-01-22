<?php

declare(strict_types=1);

namespace App\Factory\Entity\Directory;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\DirectoryItem;
use App\Entity\Item;

class DirectoryItemFactory
{
    public function create(Item $item, AbstractDirectory $directory): DirectoryItem
    {
        $directoryItem = new DirectoryItem();
        $directoryItem->setItem($item);
        $directoryItem->setDirectory($directory);

        $item->addDirectoryItem($directoryItem);
        $directory->addDirectoryItem($directoryItem);

        return $directoryItem;
    }
}
