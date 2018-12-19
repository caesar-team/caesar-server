<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Model\View\CredentialsList\CreatedItemView;

class CreatedItemViewFactory
{
    public function create(Item $item): CreatedItemView
    {
        $view = new CreatedItemView();

        $view->id = $item->getId();
        $view->lastUpdated = $item->getLastUpdated();

        return $view;
    }
}
