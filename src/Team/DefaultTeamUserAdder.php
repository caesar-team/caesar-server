<?php

declare(strict_types=1);

namespace App\Team;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;

class DefaultTeamUserAdder
{
    private TeamRepository $teamRepository;

    private UserTeamRepository $userTeamRepository;

    public function __construct(TeamRepository $teamRepository, UserTeamRepository $userTeamRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->userTeamRepository = $userTeamRepository;
    }

    public function addUser(User $user, string $role = UserTeam::DEFAULT_USER_ROLE)
    {
        $team = $this->teamRepository->getDefaultTeam();
        if (null === $team) {
            return;
        }
        if (null !== $user->getUserTeamByTeam($team)) {
            return;
        }

        $userTeam = new UserTeam($user, $team, $role);
        $user->addUserTeam($userTeam);
        $this->userTeamRepository->save($userTeam);
    }
}
