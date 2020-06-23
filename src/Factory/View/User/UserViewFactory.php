<?php

declare(strict_types=1);

namespace App\Factory\View\User;

use App\Entity\User;
use App\Model\View\User\UserView;

class UserViewFactory
{
    public function createSingle(User $user): UserView
    {
        $view = new UserView();

        $view->setId($user->getId()->toString());
        $view->setName($user->getUsername());
        $view->setAvatar($user->getAvatarLink());
        $view->setPublicKey($user->getPublicKey());
        $view->setEmail($user->getEmail());

        return $view;
    }

    /**
     * @param User[] $users
     *
     * @return UserView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
