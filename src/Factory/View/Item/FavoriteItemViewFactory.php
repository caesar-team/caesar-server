<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\FavoriteItemView;

class FavoriteItemViewFactory
{
    public function createSingle(Item $item): FavoriteItemView
    {
        $view = new FavoriteItemView();

        $view->setListId($item->getParentList()->getId()->toString());
        $view->setFavorite($item->isFavorite());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return FavoriteItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
