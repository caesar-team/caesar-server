<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\UserTeam;

class UserTeamFactory
{
    public function create(): UserTeam
    {
        return new UserTeam();
    }
}
