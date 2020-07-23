<?php

declare(strict_types=1);

namespace App\Notification;

use App\Entity\User;
use App\Notification\Model\Message;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class EmailSpoolMessenger implements MessengerInterface
{
    private Producer $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
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
        $this->producer->publish(serialize($message));
    }
}
