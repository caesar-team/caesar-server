<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Team;
use App\Model\View\Team\TeamView;
use App\Model\View\User\UserView;
use App\Strategy\ViewFactory\ListViewFactory;

class TeamViewFactory
{

    /**
     * @var ListViewFactory
     */
    private $listViewFactory;

    public function __construct(ListViewFactory $listViewFactory)
    {
        $this->listViewFactory = $listViewFactory;
    }

    public function createOne(Team $team): TeamView
    {
        $view = new TeamView();
        $view->id = $team->getId()->toString();
        $view->alias = $team->getAlias();
        $view->users = $this->extractUsers($team);
        $view->lists = $this->getLists($team);
        $view->title = $team->getTitle();

        return $view;
    }

    /**
     * @param array|Team[] $teams
     * @return TeamView[]
     */
    public function createMany(array $teams): array
    {
        $views = [];
        foreach ($teams as $group) {
            $views[] = $this->createOne($group);
        }

        return $views;
    }

    private function extractUsers(Team $group): array
    {
        $users = [];
        foreach ($group->getUserTeams() as $userGroup) {
            $user = $userGroup->getUser();
            $userView = new UserView();
            $userView->id = $user->getId();
            $userView->name = $user->getUsername();
            $userView->avatar = null === $user->getAvatar() ? null : $user->getAvatar()->getLink();
            $userView->email = $user->getEmail();
            $userView->teamsIds = $user->getTeamsIds();
            $users[] = $userView;
        }

        return $users;
    }

    private function getLists(Team $team): array
    {
        $lists = [];

        foreach ($team->getLists()->getChildLists() as $directory) {
            $lists[] = $this->listViewFactory->view($directory);
        }

        array_push($lists, $this->listViewFactory->view($team->getInbox()));
        array_push($lists, $this->listViewFactory->view($team->getTrash()));

        return $lists;
    }
}