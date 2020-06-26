<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberShortView;

class MemberShortViewFactory
{
    public function createSingle(UserTeam $userTeam): MemberShortView
    {
        if (null === $userTeam->getTeam()) {
            throw new \BadMethodCallException('UserTeam without team is invalid');
        }

        $view = new MemberShortView($userTeam->getTeam());
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
