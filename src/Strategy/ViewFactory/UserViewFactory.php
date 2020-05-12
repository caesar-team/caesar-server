<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\User;
use App\Model\View\User\UserView;

final class UserViewFactory implements ViewFactoryInterface
{
    /**
     * @param mixed $data
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     *
     * @return mixed
     */
    public function view($data)
    {
        $view = new UserView();
        $view->id = $data->getId()->toString();
        $view->email = $data->getEmail();
        $view->avatar = null === $data->getAvatar() ? null : $data->getAvatar()->getLink();
        $view->publicKey = $data->getPublicKey();
        $view->teamIds = $data->getTeamsIds();
        $view->name = $data->getUsername();

        return $view;
    }

    /**
     * @param array|User[] $data
     *
     * @return mixed
     */
    public function viewList(array $data)
    {
        $list = [];
        foreach ($data as $user) {
            $list[] = $this->view($user);
        }

        return $list;
    }
}
