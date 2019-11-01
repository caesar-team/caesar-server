<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Model\DTO\Message;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class Messenger
{
    /**
     * @var Producer
     */
    private $producer;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Producer $producer,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->producer = $producer;
    }

    /**
     * @param User $user
     * @param Message $message
     * @throws \Exception
     */
    public function send(User $user, Message $message)
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        $this->logger->debug('Registered in Messenger');
        $this->logger->debug(sprintf('a message with address %s is formed', $message->email));
        $this->producer->publish(serialize($message));
    }
}