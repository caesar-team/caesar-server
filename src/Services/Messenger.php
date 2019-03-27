<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\MessageHistory;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Repository\MessageHistoryRepository;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use App\Model\DTO\Message;

class Messenger
{
    /**
     * @var Producer
     */
    private $producer;
    /**
     * @var MessageHistoryRepository
     */
    private $historyRepository;

    public function __construct(Producer $producer, MessageHistoryRepository $historyRepository)
    {
        $this->producer = $producer;
        $this->historyRepository = $historyRepository;
    }

    /**
     * @param User $user
     * @param Message $message
     */
    public function send(User $user, Message $message)
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }
        if ($this->skipUnlessGranted($user, $message)) {
            return;
        }

        $this->producer->publish(serialize($message));
    }

    /**
     * @param User $user
     * @param Message $message
     * @return bool
     */
    private function skipUnlessGranted(User $user, Message $message): bool
    {
        $messageHistory = $this->historyRepository->findOneBy([
            'recipientId' => $user->getId()->toString(),
            'category' => MessageHistory::DEFAULT_CATEGORY,
            'code' => $message->code,
        ], [
            'createdAt' => 'DESC'
        ]);

        switch (true) {
            case !$messageHistory instanceof MessageHistory:
            case $messageHistory->getCode() !== MailRegistry::NEW_ITEM_MESSAGE:
            case (new \DateTimeImmutable())->format('Y-m-d') !== $messageHistory->getCreatedAt()->format('Y-m-d'):
                $isNotGranted = false;
                break;
            default:
                $isNotGranted = true;
        }

        return $isNotGranted;
    }
}