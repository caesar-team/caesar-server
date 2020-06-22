<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\UserTeam;

final class MemberShortView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string|null
     */
    public $role;

    public static function create(UserTeam $userTeam): self
    {
        $view = new self();
        $view->id = $userTeam->getUser()->getId()->toString();
        $view->role = $userTeam->getUserRole();

        return $view;
    }

    /**
     * @param UserTeam[] $usersTeams
     *
     * @return MemberShortView[]
     */
    public static function createMany(array $usersTeams): array
    {
        $list = [];
        foreach ($usersTeams as $usersTeam) {
            $list[] = MemberShortView::create($usersTeam);
        }

        return $list;
    }
}
