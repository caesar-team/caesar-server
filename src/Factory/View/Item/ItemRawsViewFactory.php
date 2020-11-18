<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\ItemRawsView;

class ItemRawsViewFactory
{
    public function createSingle(Item $item): ItemRawsView
    {
        $view = new ItemRawsView();
        $view->setId($item->getId()->toString());
        $view->setRaws($item->getRaws());

        return $view;
    }
}
