<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\UserTeamView;

class UserTeamViewFactory
{
    public function createSingle(UserTeam $userTeam): UserTeamView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $team = $userTeam->getTeam();

        $view = new UserTeamView($team);
        $view->setId($team->getId()->toString());
        $view->setTitle($team->getTitle());
        $view->setType($team->getAlias());
        $view->setCreatedAt($userTeam->getCreatedAt());
        $view->setUpdatedAt($userTeam->getUpdatedAt());
        $view->setUserRole($userTeam->getUserRole());
        $view->setIcon($team->getIcon());

        return $view;
    }

    /**
     * @param UserTeam[] $users
     *
     * @return UserTeamView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
