<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

use App\Notification\Model\Message;

interface MessageGrouperInterface
{
    public function support(array $events): bool;

    /**
     * @return Message[]
     */
    public function group(array &$events): array;
}
