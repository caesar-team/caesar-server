<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Message\BufferedMessage;
use App\Mailer\Sender\MailSender;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BufferedMessageHandler implements MessageHandlerInterface
{
    /**
     * @var MailSender
     */
    private $mailSender;

    public function __construct(MailSender $mailSender)
    {
        $this->mailSender = $mailSender;
    }

    public function __invoke(BufferedMessage $message)
    {
        $this->mailSender->send($message->getTemplate(), $message->getRecipients(), $this->jsonToArray($message->getContent()));
    }

    private function jsonToArray(string $content): array
    {
        try {
            $array =  json_decode($content, true);
        } catch (\Exception $exception) {
            $array = [];
        }

        return is_array($array) ? $array : [];
    }
}