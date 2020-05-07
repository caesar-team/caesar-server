<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\UserView;

class UserViewFactory
{
    public function create(User $user): UserView
    {
        $view = new UserView();

        $view->id = $user->getId();
        $view->name = $user->getUsername();
        $view->avatar = null === $user->getAvatar() ? null : $user->getAvatar()->getLink();
        $view->publicKey = $user->getPublicKey();
        $view->email = $user->getEmail();

        return $view;
    }
}
