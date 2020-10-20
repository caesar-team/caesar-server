<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Factory\View\User\UserViewFactory;
use App\Model\View\Team\MemberView;

class MemberViewFactory
{
    private UserViewFactory $userViewFactory;

    public function __construct(UserViewFactory $userViewFactory)
    {
        $this->userViewFactory = $userViewFactory;
    }

    public function createSingle(UserTeam $userTeam): MemberView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $user = $userTeam->getUser();

        $view = new MemberView($userTeam, $userTeam->getTeam());
        $view->setUser($this->userViewFactory->createSingle($user));
        $view->setTeamRole($userTeam->getUserRole());

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
