<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\User;

class LoginRequest
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $matcher;

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getMatcher(): ?string
    {
        return $this->matcher;
    }

    /**
     * @param string $matcher
     */
    public function setMatcher(string $matcher): void
    {
        $this->matcher = $matcher;
    }
}
