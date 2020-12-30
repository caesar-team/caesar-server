<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Team;

final class BatchMoveTeamItemsRequest
{
    /**
     * @var MoveTeamItemRequest[]
     */
    private array $moveItemRequests = [];

    private AbstractDirectory $directory;

    private Team $team;

    public function __construct(AbstractDirectory $directory, Team $team)
    {
        $this->directory = $directory;
        $this->team = $team;
    }

    /**
     * @return MoveTeamItemRequest[]
     */
    public function getMoveItemRequests(): array
    {
        return $this->moveItemRequests;
    }

    /**
     * @param MoveTeamItemRequest[] $moveItemRequests
     */
    public function setMoveItemRequests(array $moveItemRequests): void
    {
        $this->moveItemRequests = $moveItemRequests;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
