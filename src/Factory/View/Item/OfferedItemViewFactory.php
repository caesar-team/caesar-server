<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\OfferedItemView;

class OfferedItemViewFactory
{
    public function createSingle(Item $item): OfferedItemView
    {
        $view = new OfferedItemView();

        $view->setId($item->getId()->toString());
        $view->setType($item->getType());
        $view->setListId($item->getParentList()->getId()->toString());
        $view->setSort($item->getSort());
        $view->setSecret($item->getSecret());
        $view->setOwnerId($item->getOwner()->getId()->toString());
        $view->setOriginalItemId($item->getOriginalItem()->getId()->toString());
        $view->setLastUpdated($item->getLastUpdated());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return OfferedItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
