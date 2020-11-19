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
        $view->setAttachmentsCount($meta->getAttachmentsCount());
        $view->setWebsite($meta->getWebsite());
        $view->setTitle($meta->getTitle());

        return $view;
    }
}
