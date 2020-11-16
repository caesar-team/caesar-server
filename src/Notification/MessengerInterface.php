<?php

declare(strict_types=1);

namespace App\Notification;

use App\Notification\Model\Message;

interface MessengerInterface
{
    public function support(Message $message): bool;

    public function send(Message $message): void;
}
