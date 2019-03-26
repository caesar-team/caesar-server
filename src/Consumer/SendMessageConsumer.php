<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Model\DTO\Message;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Sylius\Component\Mailer\Sender\SenderInterface;

class SendMessageConsumer implements ConsumerInterface
{
    /**
     * @var SenderInterface
     */
    private $sender;

    public function __construct(SenderInterface $sender)
    {
        $this->sender = $sender;
    }

    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->getBody());
        if (!$message instanceof Message) {
            return;
        }
        $email = $message->email;
        $options = $message->options;
        $code = $message->emailCode;

        try {
            $this->sender->send($code, [$email], $options);
        } catch (\Exception $exception) {
        } catch (\Throwable $error) {
        }
    }
}