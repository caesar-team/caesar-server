<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberView;
use App\Model\View\Team\MemberWithHateoasView;

class MemberViewFactory
{
    public function createSingle(UserTeam $userTeam, bool $hateoas = true): MemberView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $user = $userTeam->getUser();
        if ($hateoas) {
            $view = new MemberWithHateoasView($userTeam, $userTeam->getTeam());
        } else {
            $view = new MemberView($userTeam, $userTeam->getTeam());
        }
        $view->setUserId($user->getId()->toString());
        $view->setId($userTeam->getId()->toString());
        $view->setTeamRole($userTeam->getUserRole());

        return $view;
    }

    /**
     * @param UserTeam[] $users
     *
     * @return MemberView[]
     */
    public function createCollection(array $users, bool $hateoas = true): array
    {
        return array_map(function (UserTeam $userTeam) use ($hateoas) {
            return $this->createSingle($userTeam, $hateoas);
        }, $users);
    }
}
