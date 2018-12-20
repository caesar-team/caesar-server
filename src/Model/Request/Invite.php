<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\User;

class Invite
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $secret;

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}
