<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;

class DirectoryHelper
{
    public static function hasOfferedItems(User $user, array $teams = []): bool
    {
        $teamsOfferedItemsCount = count(DirectoryHelper::extractOfferedItemsByUser($user));
        foreach ($teams as $team) {
            $teamsOfferedItemsCount = $teamsOfferedItemsCount + count(self::extractOfferedTeamsItemsByUser($user, $team));
        }

        return 0 < $teamsOfferedItemsCount;
    }

    /**
     * @return array|Item[]
     */
    public static function extractOfferedItemsByUser(User $user): array
    {
        $inbox = $user->getInbox();
        $inboxItems = array_filter($inbox->getChildItems(), [DirectoryHelper::class, 'filterByOffered']);
        $lists = $user->getLists();
        $listsItems = DirectoryHelper::getListsItems([$lists], $lists->getChildItems());
        $items = $inboxItems + $listsItems;

        return $items;
    }

    /**
     * @return array|Item[]
     */
    public static function extractOfferedTeamsItemsByUser(User $user, Team $team): array
    {
        $lists = $team->getLists();
        $items = DirectoryHelper::getListsItems([$lists], $lists->getChildItems());
        $items = array_filter($items, function (Item $item) use ($user) {
            return $user === $item->getSignedOwner();
        });

        return $items;
    }

    public static function filterByOffered(Item $item): bool
    {
        return Item::STATUS_OFFERED === $item->getStatus();
    }

    /**
     * @param array|Directory[] $directories
     *
     * @return mixed
     */
    public static function getListsItems(array $directories, array $items = []): array
    {
        foreach ($directories as $directory) {
            $items = $items + $directory->getChildItems();
            if ($directory->getChildLists()) {
                $items = $items + DirectoryHelper::getListsItems($directory->getChildLists()->toArray(), $items);
            }
        }

        return array_filter($items, [DirectoryHelper::class, 'filterByOffered']);
    }
}
