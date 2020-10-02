<?php

declare(strict_types=1);

namespace App\Model\Request\Team;

use App\Entity\User;
use App\Model\Request\CreateTeamKeypairRequest;

final class CreateVaultRequest
{
    /**
     * @var CreateTeamRequest
     */
    private $team;

    /**
     * @var CreateTeamKeypairRequest
     */
    private $keypair;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getTeam(): ?CreateTeamRequest
    {
        return $this->team;
    }

    public function setTeam(CreateTeamRequest $team): void
    {
        $this->team = $team;
    }

    public function getKeypair(): ?CreateTeamKeypairRequest
    {
        return $this->keypair;
    }

    public function setKeypair(CreateTeamKeypairRequest $keypair): void
    {
        $this->keypair = $keypair;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
