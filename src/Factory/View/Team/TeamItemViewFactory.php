<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\Team;
use App\Factory\View\Item\ItemViewFactory;
use App\Model\View\Team\TeamItemView;
use Symfony\Component\Security\Core\Security;

class TeamItemViewFactory
{
    private ItemViewFactory $itemFactory;

    private Security $security;

    public function __construct(Security $security, ItemViewFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
        $this->security = $security;
    }

    public function createSingle(Team $team): TeamItemView
    {
        $userTeam = $team->getUserTeamByUser($this->security->getUser());

        $view = new TeamItemView($team, $userTeam);
        $view->setId($team->getId()->toString());
        $view->setTitle($team->getTitle());
        $view->setIcon($team->getIcon());
        $view->setType($team->getAlias() ?: Team::OTHER_TYPE);
        $view->setItems(
            $this->itemFactory->createCollection($team->getOwnedItems())
        );

        return $view;
    }

    /**
     * @param Team[] $teams
     *
     * @return TeamItemView[]
     */
    public function createCollection(array $teams): array
    {
        return array_map([$this, 'createSingle'], $teams);
    }
}
