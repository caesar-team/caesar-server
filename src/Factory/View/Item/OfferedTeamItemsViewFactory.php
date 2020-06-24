<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Team;
use App\Model\View\Item\OfferedTeamItemsView;

class OfferedTeamItemsViewFactory
{
    private OfferedItemViewFactory $itemViewFactory;

    public function __construct(OfferedItemViewFactory $itemViewFactory)
    {
        $this->itemViewFactory = $itemViewFactory;
    }

    public function createSingle(Team $team): OfferedTeamItemsView
    {
        $view = new OfferedTeamItemsView();

        $view->setId($team->getId()->toString());
        $view->setItems(
            $this->itemViewFactory->createCollection(
                $team->getOfferedItems()
            )
        );

        return $view;
    }

    /**
     * @param Team[] $teams
     *
     * @return OfferedTeamItemsView[]
     */
    public function createCollection(array $teams): array
    {
        return array_map([$this, 'createSingle'], $teams);
    }
}
