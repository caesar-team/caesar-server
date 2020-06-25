<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\LinkedItemView;

class LinkedItemViewFactory
{
    public function createSingle(Item $item): LinkedItemView
    {
        $view = new LinkedItemView();
        $view->setId($item->getId()->toString());
        $view->setUserId($item->getOwner()->getId()->toString());
        $view->setLastUpdated($item->getLastUpdated());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return LinkedItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
