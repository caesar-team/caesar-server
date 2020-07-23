<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

use App\Entity\MessageLog;
use App\Mailer\MailRegistry;
use App\Notification\Model\Message;

class NewRegistrationMessageGrouper implements MessageGrouperInterface
{
    public function support(array $events): bool
    {
        return isset($events[MailRegistry::NEW_REGISTRATION]);
    }

    public function group(array &$events): array
    {
        /** @var MessageLog $message */
        $message = current($events[MailRegistry::NEW_REGISTRATION]);
        $emails = [];
        foreach ($events[MailRegistry::NEW_REGISTRATION] as $message) {
            $emails[] = $message->getOptions()['email'] ?? null;
        }

        $options = [
            'email' => '<li>'.implode('</li><li>', array_filter($emails)).'</li>',
        ];

        unset($events[MailRegistry::NEW_REGISTRATION]);

        return [
            new Message($message->getRecipient(), MailRegistry::NEW_REGISTRATION, $options, false),
        ];
    }
}
