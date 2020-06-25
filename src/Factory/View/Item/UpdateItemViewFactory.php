<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\ItemUpdate;
use App\Model\View\Item\UpdateItemView;

class UpdateItemViewFactory
{
    public function createSingle(ItemUpdate $item): UpdateItemView
    {
        $view = new UpdateItemView();
        $view->setSecret($item->getSecret());
        $view->setUserId($item->getUpdatedBy()->getId()->toString());
        $view->setCreatedAt($item->getLastUpdated());

        return $view;
    }
}
