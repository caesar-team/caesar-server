<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Team;

final class BatchCreateMemberRequest
{
    /**
     * @var CreateMemberRequest[]
     */
    private array $members = [];

    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return CreateMemberRequest[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param CreateMemberRequest[] $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
