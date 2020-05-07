<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Mailer\Sender\MailSender;
use App\Model\DTO\Message;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Throwable;

class SendMessageConsumer implements ConsumerInterface
{
    /**
     * @var SenderInterface|MailSender
     */
    private $sender;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(SenderInterface $sender, EntityManagerInterface $entityManager)
    {
        $this->sender = $sender;
        $this->entityManager = $entityManager;
    }

    public function execute(AMQPMessage $msg): void
    {
        $message = unserialize($msg->getBody());
        if (!$message instanceof Message) {
            return;
        }

        $email = $message->email;
        $options = $message->options;
        $code = $message->code;

        try {
            $this->sender->send($code, [$email], $options);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        } catch (Throwable $error) {
            echo $error->getMessage();
        }
    }
}
