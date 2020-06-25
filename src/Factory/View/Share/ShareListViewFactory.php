<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Model\DTO\ShareItemCollection;
use App\Model\View\Share\ShareListView;

class ShareListViewFactory
{
    private ShareViewFactory $shareViewFactory;

    public function __construct(ShareViewFactory $shareViewFactory)
    {
        $this->shareViewFactory = $shareViewFactory;
    }

    /**
     * @param ShareItemCollection[] $itemsCollection
     */
    public function createSingle(array $itemsCollection): ShareListView
    {
        $view = new ShareListView();
        $view->setShares(
            $this->shareViewFactory->createCollection($itemsCollection)
        );

        return $view;
    }
}
