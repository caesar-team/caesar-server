<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Model\DTO\Share;
use App\Model\View\Item\ShareView;

class ShareViewFactory
{
    public function createSingle(Share $item): ShareView
    {
        $view = new ShareView();
        $view->setUserId($item->getUser()->getId()->toString());
        $view->setKeypairId($item->getKeypair()->getId()->toString());

        return $view;
    }

    /**
     * @param Share[] $items
     *
     * @return ShareView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
