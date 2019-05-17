<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Entity\MessageHistory;
use App\Mailer\Sender\MailSender;
use App\Model\DTO\Message;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Sylius\Component\Mailer\Sender\SenderInterface;

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

    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->getBody());
        if (!$message instanceof Message) {
            return;
        }
        $email = $message->email;
        $options = $message->options;
        $code = $message->code;
        $recipient = $message->recipientId;

        try {
            print($this->sender->getMetaData());
            $this->sender->send($code, [$email], $options);
            $messageHistory = new MessageHistory();
            $messageHistory->setRecipientId($recipient);
            $messageHistory->setCode($code);
            $this->entityManager->persist($messageHistory);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            print($exception->getMessage());
        } catch (\Throwable $error) {
            print($error->getMessage());
        }
    }
}