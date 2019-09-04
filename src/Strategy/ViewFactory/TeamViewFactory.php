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
     * @var ListViewFactory
     */
    private $listViewFactory;

    public function __construct(ListViewFactory $listViewFactory)
    {
        $this->listViewFactory = $listViewFactory;
    }

    /**
     * @param Team $team
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
        $view->users = $this->extractUsers($team);
        if (Team::DEFAULT_GROUP_ALIAS !== $team->getAlias()) {
            $view->lists = $this->getLists($team);
        }

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