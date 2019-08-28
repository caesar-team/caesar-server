<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Team;
use App\Model\View\Team\TeamView;
use App\Model\View\User\UserView;

class TeamViewFactory
{
    public function createOne(Team $group): TeamView
    {
        $view = new TeamView();
        $view->id = $group->getId()->toString();
        $view->alias = $group->getAlias();
        $view->users = $this->extractUsers($group);
        $view->title = $group->getTitle();

        return $view;
    }

    /**
     * @param array|Team[] $groups
     * @return TeamView[]
     */
    public function createMany(array $groups): array
    {
        $views = [];
        foreach ($groups as $group) {
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
            $users[] = $userView;
        }

        return $users;
    }
}