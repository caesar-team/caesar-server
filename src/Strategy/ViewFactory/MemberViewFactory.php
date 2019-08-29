<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberView;

final class MemberViewFactory implements ViewFactoryInterface
{

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof UserTeam;
    }

    /**
     * @param UserTeam $data
     *
     * @return mixed
     */
    public function view($data)
    {
        $view = new MemberView();
        $user = $data->getUser();
        $view->id = $user->getId()->toString();
        $view->name = $user->getUsername();
        $view->email = $user->getEmail();
        $view->avatar = null === $user->getAvatar() ? null : $user->getAvatar()->getLink();
        $view->publicKey = $user->getPublicKey();
        $view->role = $data->getUserRole();

        return $view;
    }

    /**
     * @param array|UserTeam[] $data
     *
     * @return mixed
     */
    public function viewList(array $data)
    {
        $list = [];
        foreach ($data as $userTeam) {
            $list[] = $this->view($userTeam);
        }

        return $list;
    }
}