<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\User;

final class ShareSendMessageRequest
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $message;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
