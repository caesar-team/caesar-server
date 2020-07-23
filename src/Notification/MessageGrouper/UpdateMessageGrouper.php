<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

use App\Entity\MessageLog;
use App\Mailer\MailRegistry;
use App\Notification\Model\Message;

class UpdateMessageGrouper implements MessageGrouperInterface
{
    public function support(array $events): bool
    {
        return !isset($events[MailRegistry::SHARE_ITEM])
            && isset($events[MailRegistry::UPDATE_ITEM])
        ;
    }

    public function group(array &$events): array
    {
        /** @var MessageLog $message */
        $message = current($events[MailRegistry::UPDATE_ITEM]);
        $options = $message->getOptions();
        $options['update_count'] = count($events[MailRegistry::UPDATE_ITEM]);

        unset($events[MailRegistry::UPDATE_ITEM]);

        return [
            new Message($message->getRecipient(), MailRegistry::UPDATE_ITEM, $options, false),
        ];
    }
}
