<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateMemberRequest
{
    /**
     * @Assert\NotBlank()
     */
    private ?string $userRole;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    /**
     * @Assert\NotBlank()
     */
    private ?User $user;

    private Team $team;

    public function __construct(Team $team, ?User $user = null)
    {
        $this->user = $user;
        $this->team = $team;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): void
    {
        $this->userRole = $userRole;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
