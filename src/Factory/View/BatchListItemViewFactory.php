<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Model\View\CredentialsList\ShareListView;

final class BatchListItemViewFactory
{
    /**
     * @var ItemViewFactory
     */
    private $itemViewFactory;

    public function __construct(ItemViewFactory $itemViewFactory)
    {
        $this->itemViewFactory = $itemViewFactory;
    }

    public function createList(array $items): ShareListView
    {
        $view = new ShareListView();
        foreach ($items as $id => $item) {
            $view->shares[] = $this->itemViewFactory->createSharedItems($id, $item);
        }

        return $view;
    }
}
