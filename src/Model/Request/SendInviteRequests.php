<?php

declare(strict_types=1);

namespace App\Model\Request;

class SendInviteRequests
{
    /**
     * @var SendInviteRequest[]
     */
    private $messages;

    public function __construct()
    {
        $this->messages = [];
    }

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

    public function addMessage(SendInviteRequest $inviteRequest): void
    {
        $this->messages[] = $inviteRequest;
    }
}
