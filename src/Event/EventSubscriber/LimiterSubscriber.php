<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Limiter\Inspector\DatabaseSizeInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LimiterSubscriber implements EventSubscriberInterface
{
    private const DATABASE_SIZE_ROUTES = [
        'api_create_item',
        'api_batch_create_items',
        'api_child_to_item',
        'api_batch_share_item',
        'api_edit_item',
        'api_team_create_list',
        'api_team_edit_list',
        'api_team_member_add',
        'api_team_create',
        'api_create_list',
        'api_user_create',
        'api_user_batch_create',
    ];

    private LimiterInterface $limiter;

    public function __construct(LimiterInterface $limiter)
    {
        $this->limiter = $limiter;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        if (!in_array($route, self::DATABASE_SIZE_ROUTES)) {
            return;
        }

        $size = (int) $request->headers->get('content-length');
        $this->limiter->check([
            new LimitCheck(DatabaseSizeInspector::class, $size),
        ]);
    }
}
