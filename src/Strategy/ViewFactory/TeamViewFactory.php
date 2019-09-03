<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Team;
use App\Model\View\Team\TeamView;
use App\Model\View\User\UserView;
use App\Strategy\ViewFactory\ListViewFactory;

class TeamViewFactory implements ViewFactoryInterface
{

    /**
     * @var ListViewFactory
     */
    private $listViewFactory;

    public function __construct(ListViewFactory $listViewFactory)
    {
        $this->listViewFactory = $listViewFactory;
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

    /**
     * @param Team $team
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof Team;
    }

    /**
     * @param Team $team
     *
     * @return TeamView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function view($team)
    {
        $view = new TeamView();
        $view->id = $team->getId()->toString();
        $view->type = $team->getAlias();
        $view->users = $this->extractUsers($team);
        $view->lists = $this->getLists($team);
        $view->title = $team->getTitle();
        $view->icon = $team->getIcon();

        return $view;
    }

    /**
     * @param array|Team[] $teams
     *
     * @return TeamView[]
     * @throws \Doctrine\ORM\NonUniqueResultException
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