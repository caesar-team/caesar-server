<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\InviteItemView;

class InviteItemViewFactory
{
    public function createSingle(Item $item): InviteItemView
    {
        $view = new InviteItemView();
        $view->setId($item->getId()->toString());
        $view->setUserId($item->getSignedOwner()->getId()->toString());
        $view->setAccess($item->getAccess());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return InviteItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
