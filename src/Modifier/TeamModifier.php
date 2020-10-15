<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Request\Team\EditTeamRequest;

class TeamModifier
{
    private TeamRepository $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function modifyByRequest(EditTeamRequest $request): Team
    {
        $team = $request->getTeam();
        $team->setTitle($request->getTitle());
        $team->setIcon($request->getIcon());

        $this->repository->save($team);

        return $team;
    }
}
