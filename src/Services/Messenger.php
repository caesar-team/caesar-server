<?php

declare(strict_types=1);

namespace App\Services;


use App\Entity\MessageHistory;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Mailer\Sender\MailSender;
use App\Repository\MessageHistoryRepository;
use App\Model\DTO\Message;
use Doctrine\ORM\EntityManagerInterface;

class Messenger
{
    /**
     * @var MessageHistoryRepository
     */
    private $historyRepository;
    /**
     * @var MailSender
     */
    private $mailSender;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(MailSender $mailSender, MessageHistoryRepository $historyRepository, EntityManagerInterface $entityManager)
    {
        $this->historyRepository = $historyRepository;
        $this->mailSender = $mailSender;
        $this->entityManager = $entityManager;
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

        $this->mailSender->send($message->code, [$message->email], $message->options);
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