<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Notification\Model\Message;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Throwable;

class SendMessageConsumer implements ConsumerInterface
{
    private SenderInterface $sender;

    private LoggerInterface $logger;

    public function __construct(SenderInterface $sender, LoggerInterface $logger)
    {
        $this->sender = $sender;
        $this->logger = $logger;
    }

    public function execute(AMQPMessage $msg): void
    {
        $message = unserialize($msg->getBody());
        if (!$message instanceof Message) {
            $this->logger->critical('[Consumer] $message is not instance of Message');

            return;
        }

        $email = $message->getEmail();
        $options = $message->getOptions();
        $code = $message->getCode();

        try {
            $this->sender->send($code, [$email], $options);
        } catch (Throwable $exception) {
            $this->logger->critical(sprintf('[Consumer] Error: %s, Trace: %s', $exception->getMessage(), $exception->getTraceAsString()));
            echo $exception->getMessage();
        }
    }
}
