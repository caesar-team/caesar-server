<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\MessageHistory;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\DTO\Message;
use App\Repository\MessageHistoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;

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
        MessageHistoryRepository $historyRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->historyRepository = $historyRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->producer = $producer;
    }

    /**
     * @throws \Exception
     */
    public function send(User $user, Message $message): void
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }
        if ($this->skipUnlessGranted($user, $message)) {
            return;
        }

        $this->logger->debug('Registered in Messenger');
        $this->logger->debug(sprintf('a message with address %s is formed', $message->email));
        $this->producer->publish(serialize($message));
        $messageHistory = new MessageHistory();
        $messageHistory->setRecipientId($message->recipientId);
        $messageHistory->setCode($message->code);
        $this->entityManager->persist($messageHistory);
        $this->entityManager->flush();
    }

    private function skipUnlessGranted(User $user, Message $message): bool
    {
        $messageHistory = $this->historyRepository->findOneBy([
            'recipientId' => $user->getId()->toString(),
            'category' => MessageHistory::DEFAULT_CATEGORY,
            'code' => $message->code,
        ], [
            'createdAt' => 'DESC',
        ]);

        switch (true) {
            case !$messageHistory instanceof MessageHistory:
            case MailRegistry::NEW_ITEM_MESSAGE !== $messageHistory->getCode():
            case (new DateTimeImmutable())->format('Y-m-d') !== $messageHistory->getCreatedAt()->format('Y-m-d'):
                $isNotGranted = false;
                break;
            default:
                $isNotGranted = true;
        }

        return $isNotGranted;
    }
}
