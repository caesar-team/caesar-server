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
        $msg = json_decode($msg->getBody());
        $email = $msg['email'];
        $url = $msg['url'];
        $code = $msg['email_code'];
        $this->sender->send($code, [$email], [
            'url' => $url,
        ]);
    }
}