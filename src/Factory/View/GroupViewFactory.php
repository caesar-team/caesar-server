<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Group;
use App\Model\View\Group\GroupView;
use App\Model\View\User\UserView;

class GroupViewFactory
{
    public function createOne(Group $group): GroupView
    {
        $view = new GroupView();
        $view->id = $group->getId()->toString();
        $view->alias = $group->getAlias();
        $view->users = $this->extractUsers($group);
        $view->title = $group->getTitle();

        return $view;
    }

    /**
     * @param array|Group[] $groups
     * @return GroupView[]
     */
    public function createMany(array $groups): array
    {
        $views = [];
        foreach ($groups as $group) {
            $views[] = $this->createOne($group);
        }

        return $views;
    }

    private function extractUsers(Group $group): array
    {
        $users = [];
        foreach ($group->getUserGroups() as $userGroup) {
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