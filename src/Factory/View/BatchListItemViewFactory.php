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
        foreach ($items as $id => $sharedItems) {
            /** @todo candidate to refactoring */
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            $view->shares[] = $this->itemViewFactory->createSharedItems($id, $sharedItems);
        }

        return $view;
    }
}
