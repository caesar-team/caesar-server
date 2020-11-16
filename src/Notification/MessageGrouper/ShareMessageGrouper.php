<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

use App\Entity\MessageLog;
use App\Mailer\MailRegistry;
use App\Notification\Model\Message;

class ShareMessageGrouper implements MessageGrouperInterface
{
    public function support(array $events): bool
    {
        return isset($events[MailRegistry::SHARE_ITEM])
            && !isset($events[MailRegistry::UPDATE_ITEM])
        ;
    }

    public function group(array &$events): array
    {
        /** @var MessageLog $message */
        $message = current($events[MailRegistry::SHARE_ITEM]);
        $options = $message->getOptions();
        $options['share_count'] = count($events[MailRegistry::SHARE_ITEM]);

        unset($events[MailRegistry::SHARE_ITEM]);

        return [
            new Message($message->getRecipient(), MailRegistry::SHARE_ITEM, $options, false),
        ];
    }
}
