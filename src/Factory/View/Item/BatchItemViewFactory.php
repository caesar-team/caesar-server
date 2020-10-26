<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Factory\View\Team\TeamItemViewFactory;
use App\Model\DTO\GroupedUserItems;
use App\Model\View\Item\BatchItemsView;
use App\Model\View\Item\ItemView;

class BatchItemViewFactory
{
    private ItemViewFactory $itemFactory;

    private TeamItemViewFactory $teamFactory;

    public function __construct(ItemViewFactory $itemFactory, TeamItemViewFactory $teamFactory)
    {
        $this->itemFactory = $itemFactory;
        $this->teamFactory = $teamFactory;
    }

    public function createSingle(GroupedUserItems $userItems): BatchItemsView
    {
        $view = new BatchItemsView();
        $view->setPersonals($this->itemFactory->createCollection(array_values($userItems->getPersonalItems())));
        /** @psalm-suppress InvalidArgument */
        $view->setShares(
            // @todo remove after implemented inbox
            array_map(
                static function (ItemView $view) use ($userItems) {
                    $view->setListId($userItems->getUser()->getInbox()->getId()->toString());

                    return $view;
                },
                $this->itemFactory->createCollection(array_values($userItems->getSharedItems()))
            )
        );
        $view->setTeams($this->itemFactory->createCollection(array_values($userItems->getTeamItems())));
        $view->setSystems($this->itemFactory->createCollection(array_values($userItems->getSystemItems())));
        $view->setKeypairs($this->itemFactory->createCollection(array_values($userItems->getKeypairItems())));

        return $view;
    }
}
