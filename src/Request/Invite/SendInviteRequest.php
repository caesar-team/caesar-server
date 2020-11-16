<?php

declare(strict_types=1);

namespace App\Request\Invite;

use App\Entity\User;

final class SendInviteRequest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $teamIds = [];

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTeamIds(): array
    {
        return $this->teamIds;
    }

    public function setTeamIds(array $teamIds): void
    {
        $this->teamIds = $teamIds;
    }
}
