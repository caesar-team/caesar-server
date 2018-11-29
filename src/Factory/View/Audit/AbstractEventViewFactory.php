<?php

declare(strict_types=1);

namespace App\Factory\View\Audit;

use App\Entity\Audit\AbstractEvent;
use App\Model\View\Audit\AbstractEventView;

abstract class AbstractEventViewFactory
{
    protected function injectDataEvent(AbstractEventView $view, AbstractEvent $event): AbstractEventView
    {
        $view->id = $event->getId();
        $view->blame = $event->getBlame();
        $view->ip = $event->getIp();
        $view->message = $event->getMessage();
        $view->createdAt = $event->getCreatedAt()->format('d-m-Y H:i:s');
        $view->verify = $event->isVerify();

        return $view;
    }
}
