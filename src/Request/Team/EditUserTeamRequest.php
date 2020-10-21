<?php

declare(strict_types=1);

namespace App\Request\Team;

final class EditUserTeamRequest
{
    /**
     * @var string|null
     */
    private $teamRole;

    public function getTeamRole(): ?string
    {
        return $this->teamRole;
    }

    public function setTeamRole(?string $teamRole): void
    {
        $this->teamRole = $teamRole;
    }
}
