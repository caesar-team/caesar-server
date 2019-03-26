<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use App\Model\DTO\Message;

class Messenger
{
    /**
     * @var Producer
     */
    private $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function send(User $user, Message $message)
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        $this->producer->publish(serialize($message));
    }
}