<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Factory\View\Item\ChildItemViewFactory;
use App\Model\DTO\ShareItemCollection;
use App\Model\View\Share\ShareView;

class ShareViewFactory
{
    private ChildItemViewFactory $childItemViewFactory;

    public function __construct(ChildItemViewFactory $childItemViewFactory)
    {
        $this->childItemViewFactory = $childItemViewFactory;
    }

    public function createSingle(ShareItemCollection $shareItemCollection): ShareView
    {
        $view = new ShareView();
        $view->setOriginalItemId($shareItemCollection->getId());
        $view->setItems(
            $this->childItemViewFactory->createCollection(
                $shareItemCollection->getItems()
            )
        );

        return $view;
    }

    /**
     * @param ShareItemCollection[] $items
     *
     * @return ShareView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
