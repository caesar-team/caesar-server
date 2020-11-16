<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use App\Repository\TeamRepository;

final class TeamCountInspector extends AbstractInspector implements InspectorInterface
{
    private TeamRepository $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUsed(int $addedSize = 0): int
    {
        return $this->repository->getCountTeams() + $addedSize;
    }

    public function getErrorMessage(): string
    {
        return 'limiter.exception.team_count';
    }
}
