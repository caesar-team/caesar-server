<?php

declare(strict_types=1);

namespace App\Team;

use App\Entity\Team;
use App\Entity\User;

interface AwareOwnerAndTeamInterface
{
    public function getOwner(): ?User;

    public function getTeam(): ?Team;
}
