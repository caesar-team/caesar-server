<?php

declare(strict_types=1);

namespace App\Mailer;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Sylius\Component\Mailer\Model\EmailInterface;
use Sylius\Component\Mailer\Renderer\RenderedEmail;
use Sylius\Component\Mailer\Sender\Adapter\AbstractAdapter;
use Psr\Log\LoggerInterface;

class RabbitMqMailerAdapter extends AbstractAdapter
{
    /**
     * @var Producer
     */
    private $producer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Producer $producer, LoggerInterface $logger)
    {
        $this->producer = $producer;
        $this->logger = $logger;
    }

    public function send(
        array $recipients,
        string $senderAddress,
        string $senderName,
        RenderedEmail $renderedEmail,
        EmailInterface $email,
        array $data,
        array $attachments = [],
        array $replyTo = []
    ): void
    {
        $message = [];
        $message['recipients'] = $recipients;
        $message['senderAddress'] = $senderAddress;
        $message['senderName'] = $senderName;
        $message['subject'] = $email->getSubject();
        $message['body'] = $renderedEmail->getBody();
        $message['data'] = $data;

        $this->producer->setContentType('application/json');
        $this->producer->publish(json_encode($message, JSON_PRETTY_PRINT));

        $this->logger->debug('Registred in RabbitMqMailerAdapter');
        $this->logger->debug(sprintf('%s sent a message', $senderName));
    }
}