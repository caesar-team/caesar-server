<?php

declare(strict_types=1);

namespace App\Consumer;

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
        $msg = json_decode($msg->getBody(), true);
        $email = $msg['email'];
        $options = $msg['options'];
        $code = $msg['email_code'];
        try {
            $this->sender->send($code, [$email], $options);
        } catch (\Exception $exception) {
        } catch (\Throwable $error) {
        }
    }
}