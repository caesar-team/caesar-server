<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\UserTeam;

final class MemberView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string|null
     */
    public $avatar;

    /**
     * @var string|null
     */
    public $publicKey;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $teamIds;

    /**
     * @var string|null
     */
    public $role;

    public static function create(UserTeam $userTeam): self
    {
        $view = new self();
        $user = $userTeam->getUser();
        $view->id = $user->getId()->toString();
        $view->name = $user->getUsername();
        $view->email = $user->getEmail();
        $view->avatar = null === $user->getAvatar() ? null : $user->getAvatar()->getLink();
        $view->publicKey = $user->getPublicKey();
        $view->role = $userTeam->getUserRole();
        $view->teamIds = $user->getTeamsIds();

        return $view;
    }

    /**
     * @param array|UserTeam[] $usersTeams
     *
     * @return array|MemberView[]
     */
    public static function createMany(array $usersTeams): array
    {
        $list = [];
        foreach ($usersTeams as $usersTeam) {
            $list[] = MemberView::create($usersTeam);
        }

        return $list;
    }
}
