<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\ChildItemView;

class ChildItemViewFactory
{
    public function createSingle(Item $item): ChildItemView
    {
        $view = new ChildItemView();
        $view->setId($item->getId()->toString());
        $view->setUserId($item->getSignedOwner()->getId()->toString());
        $view->setTeamId($item->getTeamId());
        $view->setLastUpdated($item->getLastUpdated());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return ChildItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
