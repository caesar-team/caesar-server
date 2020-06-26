<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\Directory;
use App\Factory\View\ItemListViewFactory;
use App\Model\View\Team\TeamListView;
use App\Repository\TeamRepository;

class TeamListViewFactory
{
    private ItemListViewFactory $itemListViewFactory;

    private TeamRepository $teamRepository;

    public function __construct(ItemListViewFactory $itemListViewFactory, TeamRepository $teamRepository)
    {
        $this->itemListViewFactory = $itemListViewFactory;
        $this->teamRepository = $teamRepository;
    }

    public function createSingle(Directory $directory): TeamListView
    {
        $view = new TeamListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getType());
        $view->setSort($directory->getSort());
        $view->setChildren(
            $this->itemListViewFactory->create($directory->getChildItems())
        );
        $team = $this->teamRepository->findOneByDirectory($directory);
        $view->setTeamId($team ? $team->getId()->toString() : null);

        return $view;
    }

    /**
     * @param Directory[] $directories
     *
     * @return TeamListView[]
     */
    public function createCollection(array $directories): array
    {
        return array_map([$this, 'createSingle'], $directories);
    }
}
