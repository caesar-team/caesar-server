<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\MessageLog;
use App\Notification\Model\Message;

class MessageLogFactory
{
    public function createFromMessage(Message $message): MessageLog
    {
        $object = new MessageLog();
        $object->setRecipient($message->getEmail());
        $object->setDeferred($message->isDeferred());
        $object->setEvent($message->getCode());
        $object->setOptions($message->getOptions());

        if (!$message->isDeferred()) {
            $object->setSentAt(new \DateTimeImmutable());
        }

        return $object;
    }
}
