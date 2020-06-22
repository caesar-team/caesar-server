<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;

final class ItemExtractor
{
    /**
     * @return array|Item[]
     */
    public static function getTeamItemsForUser(Team $team, User $user): array
    {
        $items = $team->getTrash()->getChildItems();
        foreach ($team->getLists()->getChildLists() as $directory) {
            $items = array_merge($items, $directory->getChildItems());
        }

        $items = self::filterByUser($items, $user);

        return $items;
    }

    /**
     * @param array|Item[] $items
     *
     * @return array|Item[]
     */
    private static function filterByUser(array $items, User $user): array
    {
        return array_filter($items, function (Item $item) use ($user) {
            return $item->getSignedOwner() === $user;
        });
    }
}
