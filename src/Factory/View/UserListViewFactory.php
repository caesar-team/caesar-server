<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\Response\PaginatedList;
use App\Model\View\User\UserView;

class UserListViewFactory
{
    private UserViewFactory $userViewFactory;

    public function __construct(UserViewFactory $userViewFactory)
    {
        $this->userViewFactory = $userViewFactory;
    }

    /**
     * @return UserView[]
     */
    public function create(PaginatedList $list): array
    {
        $userViewCollection = [];
        /** @var User $user */
        foreach ($list->getData() as $user) {
            $userViewCollection[] = $this->userViewFactory->create($user);
        }

        return $userViewCollection;
    }
}
