<?php

declare(strict_types=1);

namespace App\Notification;

use App\Entity\User;
use App\Factory\Entity\MessageLogFactory;
use App\Notification\Model\Message;
use App\Repository\MessageLogRepository;

class DatabaseMessenger implements MessengerInterface
{
    private MessageLogFactory $factory;

    private MessageLogRepository $repository;

    public function __construct(MessageLogFactory $factory, MessageLogRepository $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    public function support(Message $message): bool
    {
        $user = $message->getUser();
        if (null !== $user && $user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return false;
        }

        return $message->isStorage();
    }

    public function send(Message $message): void
    {
        $log = $this->factory->createFromMessage($message);

        $this->repository->save($log);
    }
}
