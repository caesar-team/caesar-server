<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberView;

class MemberViewFactory
{
    public function createSingle(UserTeam $userTeam): MemberView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $user = $userTeam->getUser();

        $view = new MemberView($userTeam->getTeam());
        $view->setId($user->getId()->toString());
        $view->setName($user->getUsername());
        $view->setEmail($user->getEmail());
        $view->setAvatar($user->getAvatarLink());
        $view->setPublicKey($user->getPublicKey());
        $view->setRole($userTeam->getUserRole());
        $view->setTeamIds($user->getTeamsIds());

        return $view;
    }

    /**
     * @param UserTeam[] $users
     *
     * @return MemberView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
