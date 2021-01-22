<?php

declare(strict_types=1);

namespace App\Event\Team;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class LeaveTeamEvent extends Event
{
    private Team $team;

    private User $user;

    public function __construct(Team $team, User $user)
    {
        $this->team = $team;
        $this->user = $user;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
