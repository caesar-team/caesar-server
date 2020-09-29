<?php

declare(strict_types=1);

namespace App\Team;

use App\Entity\User;

interface UserTeamCollectorInterface
{
    /**
     * @return User[]
     */
    public function collect(): array;
}
