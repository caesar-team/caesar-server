<?php

declare(strict_types=1);

namespace App\Factory\View\User;

use App\Entity\User;
use App\Model\View\User\SearchUserView;

final class SearchUserViewFactory
{
    public function createSingle(User $user): SearchUserView
    {
        $view = new SearchUserView();
        $view->setId($user->getId()->toString());
        $view->setEmail($user->getEmail());
        $view->setName($user->getUsername());
        $view->setAvatar($user->getAvatarLink());
        $view->setTeamIds($user->getTeamsIds());

        return $view;
    }

    /**
     * @param User[] $data
     *
     * @return SearchUserView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
