<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\User;
use App\Factory\View\Team\TeamItemViewFactory;
use App\Model\View\Item\BatchItemsView;

class BatchItemViewFactory
{
    private ItemViewFactory $itemFactory;

    private TeamItemViewFactory $teamFactory;

    public function __construct(ItemViewFactory $itemFactory, TeamItemViewFactory $teamFactory)
    {
        $this->itemFactory = $itemFactory;
        $this->teamFactory = $teamFactory;
    }

    public function createSingle(User $user): BatchItemsView
    {
        $view = new BatchItemsView();
        $view->setPersonal($this->itemFactory->createCollection(
            $user->getPersonalItems()
        ));
        $view->setTeams($this->teamFactory->createCollection(
            $user->getTeams()
        ));

        return $view;
    }
}
