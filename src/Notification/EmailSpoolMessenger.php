<?php

declare(strict_types=1);

namespace App\Notification;

use App\Entity\User;
use App\Notification\Model\Message;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailSpoolMessenger implements MessengerInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function support(Message $message): bool
    {
        $user = $message->getUser();
        if (null !== $user && $user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return false;
        }

        return !$message->isDeferred();
    }

    public function send(Message $message): void
    {
        $this->bus->dispatch($message);
    }
}
