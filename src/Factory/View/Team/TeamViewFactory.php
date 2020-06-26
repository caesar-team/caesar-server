<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\Team;
use App\Model\View\Team\TeamView;

class TeamViewFactory
{
    private MemberShortViewFactory $memberShortViewFactory;

    public function __construct(MemberShortViewFactory $memberShortViewFactory)
    {
        $this->memberShortViewFactory = $memberShortViewFactory;
    }

    public function createSingle(Team $team): TeamView
    {
        $view = new TeamView($team);
        $view->setId($team->getId()->toString());
        $view->setUsers(
            $this->memberShortViewFactory->createCollection(
                $team->getUserTeamsWithoutPretender()
            )
        );
        $view->setTitle($team->getTitle());
        $view->setIcon($team->getIcon());
        $view->setType($team->getAlias() ?: 'other');

        return $view;
    }

    /**
     * @param Team[] $teams
     *
     * @return TeamView[]
     */
    public function createCollection(array $teams): array
    {
        return array_map([$this, 'createSingle'], $teams);
    }
}
