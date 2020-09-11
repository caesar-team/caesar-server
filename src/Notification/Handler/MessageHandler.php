<?php

declare(strict_types=1);

namespace App\Notification\Handler;

use App\Notification\Model\Message;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MessageHandler implements MessageHandlerInterface
{
    private SenderInterface $sender;

    private LoggerInterface $logger;

    public function __construct(SenderInterface $sender, LoggerInterface $logger)
    {
        $this->sender = $sender;
        $this->logger = $logger;
    }

    public function __invoke(Message $message)
    {
        $email = $message->getEmail();
        $options = $message->getOptions();
        $code = $message->getCode();

        try {
            $this->sender->send($code, [$email], $options);
        } catch (\Throwable $exception) {
            $this->logger->critical(sprintf('[Consumer] Error: %s, Trace: %s', $exception->getMessage(), $exception->getTraceAsString()));
            echo $exception->getMessage();
        }
    }
}
