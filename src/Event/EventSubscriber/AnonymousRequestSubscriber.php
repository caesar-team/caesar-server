<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class AnonymousRequestSubscriber implements EventSubscriberInterface
{
    public const AVAILABLE_ROUTES = [
        'api_item_check_shared_item',
        'api_user_security_bootstrap',
        'api_srp_login_prepare',
        'api_srp_login',
        'api_keys_list',
        'api_list_tree',
        'api_user_get_info',
        'api_show_item',
    ];
    /**
     * @var Security
     */
    private $security;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
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

        $message = $this->translator->trans('app.exception.unavailable_request', ['route' => $route]);
        throw new BadRequestHttpException($message);
    }
}
