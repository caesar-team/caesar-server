<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;
use App\Entity\User;

class ChildItem
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
     * @var string
     */
    private $access;
    /**
     * @var string
     */
    private $cause = Item::CAUSE_INVITE;
    /**
     * @var string|null
     */
    private $link;

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

    public function getAccess(): ?string
    {
        return $this->access;
    }

    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getCause(): string
    {
        return $this->cause;
    }

    /**
     * @param string $cause
     */
    public function setCause(string $cause): void
    {
        $this->cause = $cause;
    }

    /**
     * @return null|string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param null|string $link
     */
    public function setLink(?string $link): void
    {
        $this->link = $link;
    }
}
