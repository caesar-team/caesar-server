<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

use App\Entity\MessageLog;
use App\Notification\Model\Message;

class DefaultMessageGrouper implements MessageGrouperInterface
{
    public function support(array $events): bool
    {
        return true;
    }

    public function group(array &$events): array
    {
        $messages = [];
        foreach ($events as $eventName => $eventMessages) {
            /** @var MessageLog $message */
            $message = current($eventMessages);
            $messages[] = Message::createMessageFromMessageLog($message);

            unset($events[$eventName]);
        }

        return $messages;
    }
}
