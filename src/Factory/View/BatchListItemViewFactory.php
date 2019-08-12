<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Model\View\CredentialsList\ShareListView;

final class BatchListItemViewFactory
{
    /**
     * @var BatchItemViewFactory
     */
    private $itemViewFactory;

    public function __construct(BatchItemViewFactory $itemViewFactory)
    {
        $this->itemViewFactory = $itemViewFactory;
    }

    /**
     * @param array $items
     * @return ShareListView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createList(array $items): ShareListView
    {
        $view = new ShareListView();
        foreach ($items as $id => $item)
        {
            $view->shares[] = $this->itemViewFactory->create($items);
        }

        return $view;
    }
}