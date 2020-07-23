<?php

declare(strict_types=1);

namespace App\Notification;

use App\Notification\Model\Message;

class CompositeMessenger implements MessengerInterface
{
    /**
     * @var MessengerInterface[]
     */
    private array $messengers;

    public function __construct(MessengerInterface ...$messengers)
    {
        $this->messengers = $messengers;
    }

    public function support(Message $message): bool
    {
        return true;
    }

    public function send(Message $message): void
    {
        foreach ($this->messengers as $messenger) {
            if ($messenger->support($message)) {
                $messenger->send($message);
            }
        }
    }
}
