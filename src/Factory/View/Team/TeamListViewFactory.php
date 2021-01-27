<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\Directory\DirectoryItem;
use App\Entity\Directory\TeamDirectory;
use App\Factory\View\ItemListViewFactory;
use App\Model\View\Team\TeamListView;

class TeamListViewFactory
{
    private ItemListViewFactory $itemListViewFactory;

    public function __construct(ItemListViewFactory $itemListViewFactory)
    {
        $this->itemListViewFactory = $itemListViewFactory;
    }

    public function createSingle(TeamDirectory $directory): TeamListView
    {
        $view = new TeamListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getType());
        $view->setSort($directory->getSort());
        $view->setChildren(array_map(function (DirectoryItem $item) {
            return $item->getItem()->getId()->toString();
        }, $directory->getDirectoryItems()));
        $view->setTeamId($directory->getTeam()->getId()->toString());

        return $view;
    }

    /**
     * @param TeamDirectory[] $directories
     *
     * @return TeamListView[]
     */
    public function createCollection(array $directories): array
    {
        return array_map([$this, 'createSingle'], $directories);
    }
}
