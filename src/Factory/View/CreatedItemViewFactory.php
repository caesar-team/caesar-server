<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Model\View\CredentialsList\CreatedItemView;

class CreatedItemViewFactory
{
    public function createSingle(Item $item): CreatedItemView
    {
        $view = new CreatedItemView();

        $view->setId($item->getId()->toString());
        $view->setLastUpdated($item->getLastUpdated());

        return $view;
    }
}
