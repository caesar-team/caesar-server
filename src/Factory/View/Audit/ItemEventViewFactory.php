<?php

declare(strict_types=1);

namespace App\Factory\View\Audit;

use App\Entity\Audit\ItemEvent;
use App\Model\View\Audit\ItemEventView;

class ItemEventViewFactory extends AbstractEventViewFactory
{
    public function create(ItemEvent $event): ItemEventView
    {
        $eventView = new ItemEventView();
        $eventView->item = $event->getItem()->getId();
        $eventView->originalItem = $event->getItem()->getOriginalItem()
            ? $event->getItem()->getOriginalItem()->getId()
            : null
        ;

        $this->injectDataEvent($eventView, $event);

        return $eventView;
    }
}
