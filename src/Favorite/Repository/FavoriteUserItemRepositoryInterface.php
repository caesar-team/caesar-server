<?php

declare(strict_types=1);

namespace App\Favorite\Repository;

use App\Entity\FavoriteUserItem;
use App\Entity\Item;
use App\Entity\User;

interface FavoriteUserItemRepositoryInterface
{
    public function findFavorite(User $user, Item $item): ?FavoriteUserItem;

    public function save(FavoriteUserItem $favorite): void;

    public function delete(FavoriteUserItem $favorite): void;
}
