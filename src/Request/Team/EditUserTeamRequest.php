<?php

declare(strict_types=1);

namespace App\Request\Team;

final class EditUserTeamRequest
{
    /**
     * @var string|null
     */
    private $userRole;

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): void
    {
        $this->userRole = $userRole;
    }
}
