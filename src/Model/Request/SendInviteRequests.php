<?php

declare(strict_types=1);

namespace App\Model\Request;

class SendInviteRequests
{
    /**
     * @var SendInviteRequest[]
     */
    private $messages;

    /**
     * @return SendInviteRequest[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param SendInviteRequest[] $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }
}