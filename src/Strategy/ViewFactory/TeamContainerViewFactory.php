<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Directory;
use App\Entity\Team;
use App\Model\DTO\OfferedTeamContainer;
use App\Model\View\Team\TeamItemsView;
use App\Utils\DirectoryHelper;

final class TeamContainerViewFactory implements ViewFactoryInterface
{
    /**
     * @var ItemViewFactory
     */
    private $itemViewFactory;

    public function __construct(ItemViewFactory $itemViewFactory)
    {
        $this->itemViewFactory = $itemViewFactory;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof OfferedTeamContainer;
    }

    /**
     * @param OfferedTeamContainer $data
     *
     * @return TeamItemsView
     */
    public function view($data)
    {
        $team = $data->getTeam();
        $view = new TeamItemsView();
        $view->id = $team->getId()->toString();
        $items = $this->getAllItems($team);
        $items = array_filter($items, [DirectoryHelper::class, 'filterByOffered']);
        $view->items = $this->itemViewFactory->viewList($items);

        return $view;
    }

    /**
     * @param array|OfferedTeamContainer[] $containers
     *
     * @return TeamItemsView[]
     */
    public function viewList(array $containers)
    {
        $list = [];
        foreach ($containers as $teamContainer) {
            $list[] = $this->view($teamContainer);
        }

        return $list;
    }

    private function getAllItems(Team $team): array
    {
        /** @var Directory[] $directories */
        $directories = $team->getLists()->getChildLists()->toArray();
        array_push($directories, $team->getTrash());

        $items = [];
        foreach ($directories as $directory) {
            if (0 === count($directory->getChildItems())) {
                continue;
            }

            foreach ($directory->getChildItems() as $childItem) {
                $items[] = $childItem;
            }
        }

        return $items;
    }
}