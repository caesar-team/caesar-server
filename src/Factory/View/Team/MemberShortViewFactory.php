<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberShortView;
use Symfony\Component\Security\Core\Security;

class MemberShortViewFactory
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function createSingle(UserTeam $userTeam): MemberShortView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $currentUserTeam = $userTeam->getTeam()->getUserTeamByUser($this->security->getUser());

        $view = new MemberShortView($currentUserTeam);
        $view->setId($userTeam->getUser()->getId()->toString());
        $view->setRole($userTeam->getUserRole());

        return $view;
    }

    /**
     * @param UserTeam[] $users
     *
     * @return MemberShortView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
