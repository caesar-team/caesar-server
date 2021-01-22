<?php

declare(strict_types=1);

namespace App\EventSubscriber\System;

use App\Event\Item\ItemsDateRefreshEvent;
use App\Item\ItemDateRefresherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemLastUpdateSubscriber implements EventSubscriberInterface
{
    private ItemDateRefresherInterface $dateRefresher;

    public function __construct(ItemDateRefresherInterface $dateRefresher)
    {
        $this->dateRefresher = $dateRefresher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
           ItemsDateRefreshEvent::class => 'onDateRefresh',
        ];
    }

    public function onDateRefresh(ItemsDateRefreshEvent $event): void
    {
        foreach ($event->getItems() as $item) {
            $this->dateRefresher->refreshDate($item);
        }
    }
}
