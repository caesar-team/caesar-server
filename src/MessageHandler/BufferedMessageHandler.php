<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Message\BufferedMessage;
use App\Repository\BufferedMessageRepository;
use App\Repository\UserRepository;
use App\Services\Messenger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BufferedMessageHandler implements MessageHandlerInterface
{
    /**
     * @var BufferedMessageRepository
     */
    private $bufferedMessageRepository;
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        BufferedMessageRepository $bufferedMessageRepository,
        Messenger $messenger,
        UserRepository $userRepository
    )
    {
        $this->bufferedMessageRepository = $bufferedMessageRepository;
        $this->messenger = $messenger;
        $this->userRepository = $userRepository;
    }

    public function __invoke(BufferedMessage $message)
    {
        $message->setCheckSum($message->createCheckSum());
        /** @var BufferedMessage $existsMessage */
        $existsMessage = $this->bufferedMessageRepository->findOneByCheckSum($message->getCheckSum());
        if ($existsMessage && $this->isTodayMessage($existsMessage->getCreatedAt())) {
            return;
        }

        $this->bufferedMessageRepository->persist($message);
        $this->bufferedMessageRepository->flush();
    }

    private function isTodayMessage(\DateTime $existsCreatedAt): bool
    {
        return $existsCreatedAt->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
    }
}