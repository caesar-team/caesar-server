<?php

declare(strict_types=1);

namespace App\Notification\MessageGrouper;

class CompositeMessageGrouper implements MessageGrouperInterface
{
    /**
     * @var MessageGrouperInterface[]
     */
    private array $groupers;

    public function __construct(MessageGrouperInterface ...$groupers)
    {
        $this->groupers = $groupers;
    }

    public function support(array $events): bool
    {
        return true;
    }

    public function group(array &$events): array
    {
        $messages = [];
        foreach ($this->groupers as $grouper) {
            if (!$grouper->support($events)) {
                continue;
            }

            $messages = array_merge($messages, $grouper->group($events));
        }

        return $messages;
    }
}
