<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CreateMemberRequest
{
    /**
     * @Assert\NotBlank()
     */
    private ?string $teamRole;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    /**
     * @Assert\NotBlank()
     */
    private ?User $user;

    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getTeamRole(): ?string
    {
        return $this->teamRole;
    }

    public function setTeamRole(?string $teamRole): void
    {
        $this->teamRole = $teamRole;
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

    /**
     * @Assert\Callback
     */
    public function callback(ExecutionContextInterface $context)
    {
        if (null !== $this->team->getUserTeamByUser($this->user)) {
            $context
                ->buildViolation('team.member.unique')
                ->atPath('user')
                ->addViolation();
        }
    }
}
