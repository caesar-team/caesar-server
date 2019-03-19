<?php

declare(strict_types=1);


namespace App\Utils;


use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;

class DirectoryHelper
{
    static public function hasOfferedItems(User $user): bool
    {
        return 0 < count(DirectoryHelper::extractOfferedItems($user));
    }

    static public function extractOfferedItems(User $user): array
    {
        $inbox = $user->getInbox();
        $inboxItems = array_filter($inbox->getChildItems(), [DirectoryHelper::class, 'filterByOffered']);
        $lists = $user->getLists();
        $listsItems = DirectoryHelper::getListsItems([$lists], $lists->getChildItems());
        $items = $inboxItems + $listsItems;

        return $items;
    }

    static public function filterByOffered (Item $item) {
        return Item::STATUS_OFFERED === $item->getStatus();
    }

    /**
     * @param array|Directory[] $directories
     * @param array $items
     * @return mixed
     */
    static public function getListsItems(array $directories, array $items = []): array
    {
        foreach ($directories as $directory) {
            $items = $items + $directory->getChildItems();
            if ($directory->getChildLists()) {
                $items = $items + DirectoryHelper::getListsItems($directory->getChildLists()->toArray(), $items);
            }
        }

        return $items;
    }
}