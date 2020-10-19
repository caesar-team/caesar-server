<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Factory\View\User\UserViewFactory;
use App\Model\View\Team\MemberView;
use Symfony\Component\Security\Core\Security;

class MemberViewFactory
{
    private Security $security;

    private UserViewFactory $userViewFactory;

    public function __construct(Security $security, UserViewFactory $userViewFactory)
    {
        $this->security = $security;
        $this->userViewFactory = $userViewFactory;
    }

    public function createSingle(UserTeam $userTeam): MemberView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $currentUserTeam = $userTeam->getTeam()->getUserTeamByUser($this->security->getUser());
        $user = $userTeam->getUser();

        $view = new MemberView($currentUserTeam, $userTeam->getTeam());
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
