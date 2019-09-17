<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Team;

class TeamContainer
{
    /**
     * @var Team
     */
    private $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @param array|Team[] $teams
     * @return array|self[]
     */
    public static function createMany(array $teams): array
    {
        $containers = [];
        foreach ($teams as $team) {
            $containers[] = new self($team);
        }

        return $containers;
    }
}