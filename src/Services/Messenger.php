<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\User;
use App\Model\DTO\Message;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

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