<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\Response\PaginatedList;
use App\Model\View\User\UserView;

class UserListViewFactory
{
    /**
     * @param PaginatedList $list
     *
     * @return UserView[]
     */
    public function create(PaginatedList $list): array
    {
        $userViewCollection = [];
        /** @var User $user */
        foreach ($list->getData() as $user) {
            $view = new UserView();

            $view->id = $user->getId();
            $view->name = $user->getUsername();
            $view->avatar = null === $user->getAvatar() ? null : $user->getAvatar()->getLink();
            $view->publicKey = $user->getPublicKey();

            $userViewCollection[] = $view;
        }

        return $userViewCollection;
    }
}
