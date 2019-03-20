<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;


use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class AnonymousRequestSubscriber implements EventSubscriberInterface
{
    const AVAILABLE_ROUTES = [
        'api_item_check_shared_item',
        'api_user_security_bootstrap',
        'api_srp_login_prepare',
        'api_srp_login',
        'api_keys_list',
        'api_list_tree',
    ];
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        if (!$user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        if (in_array($route, self::AVAILABLE_ROUTES)) {
            return;
        }

        throw new BadRequestHttpException(sprintf('Unavailable request: %s', $route));
    }
}