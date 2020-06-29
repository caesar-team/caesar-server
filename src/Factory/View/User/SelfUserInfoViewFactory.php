<?php

declare(strict_types=1);

namespace App\Factory\View\User;

use App\Entity\User;
use App\Model\View\User\SelfUserInfoView;

class SelfUserInfoViewFactory
{
    public function createSingle(User $user): SelfUserInfoView
    {
        $view = new SelfUserInfoView($user);

        $view->setId($user->getId()->toString());
        $view->setEmail($user->getEmail());
        $view->setName($user->getUsername());
        $view->setAvatar($user->getAvatarLink());
        $view->setRoles($user->getRoles());
        $view->setTeamIds($user->getTeamsIds());

        return $view;
    }
}
