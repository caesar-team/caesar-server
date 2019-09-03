<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\UserTeam;
use App\Model\View\Team\UserTeamView;

final class UserTeamViewFactory implements ViewFactoryInterface
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
     * @return UserTeamView
     */
    public function view($data)
    {
        $view = new UserTeamView();
        $view->id = $data->getTeam()->getId()->toString();
        $view->title = $data->getTeam()->getTitle();
        $view->type = $data->getTeam()->getHashtag();
        $view->createdAt = $data->getCreatedAt();
        $view->updatedAt = $data->getUpdatedAt();
        $view->userRole = $data->getUserRole();

        return $view;
    }

    /**
     * @param array|UserTeam[] $data
     *
     * @return UserTeamView[]
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