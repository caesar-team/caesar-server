<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Team;
use App\Entity\UserTeam;
use App\Model\View\Team\MemberShortView;
use App\Model\View\Team\TeamView;

class TeamViewFactory implements ViewFactoryInterface
{
    /**
     * @return array|MemberShortView[]
     */
    private function extractUsers(Team $team): array
    {
        $userTeams = $team->getUserTeams()->toArray();
        $userTeams = array_filter($userTeams, function (UserTeam $userTeam) {
            return UserTeam::USER_ROLE_PRETENDER !== $userTeam->getUserRole();
        });

        return MemberShortView::createMany($userTeams);
    }

    /**
     * @param mixed $data
     */
    public function canView($data): bool
    {
        return $data instanceof Team;
    }

    /**
     * @param Team $team
     */
    public function view($team): TeamView
    {
        $view = new TeamView();
        $view->id = $team->getId()->toString();
        $view->users = $this->extractUsers($team);

        $view->title = $team->getTitle();
        $view->icon = $team->getIcon();
        $view->type = $team->getAlias() ?: 'other';
        $view->setTeam($team);

        return $view;
    }

    /**
     * @param array|Team[] $teams
     *
     * @return TeamView[]
     */
    public function viewList(array $teams): array
    {
        $views = [];
        foreach ($teams as $team) {
            $views[] = $this->view($team);
        }

        return $views;
    }
}
