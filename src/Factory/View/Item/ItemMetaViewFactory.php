<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Embedded\ItemMeta;
use App\Model\View\Item\ItemMetaView;

class ItemMetaViewFactory
{
    public function createSingle(ItemMeta $meta): ItemMetaView
    {
        $view = new ItemMetaView();
        $view->setAttachCount($meta->getAttachCount());
        $view->setWebSite($meta->getWebSite());

        return $view;
    }
}
