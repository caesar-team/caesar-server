<?php

declare(strict_types=1);

namespace App\Factory\View\Audit;

use App\Entity\Audit\PostEvent;
use App\Model\View\Audit\PostEventView;

class PostEventViewFactory extends AbstractEventViewFactory
{
    public function create(PostEvent $event): PostEventView
    {
        $eventView = new PostEventView();
        $eventView->post = $event->getPost()->getId();
        $eventView->originalPost = $event->getPost()->getOriginalPost()
            ? $event->getPost()->getOriginalPost()->getId()
            : null
        ;

        $this->injectDataEvent($eventView, $event);

        return $eventView;
    }
}