<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\MessageHistory;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Repository\MessageHistoryRepository;
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
    )
    {
        $this->historyRepository = $historyRepository;
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