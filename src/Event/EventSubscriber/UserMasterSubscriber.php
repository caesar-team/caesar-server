<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class UserMasterSubscriber implements EventSubscriberInterface
{
    private const GRANTED_ROUTES = [
        'api_keys_save',
        'api_keys_list',
        'api_user_security_bootstrap',
        'api_anonymous_share_check',
        'hwi_oauth_service_redirect',
        'google_login',
        'api_security_2fa_code',
        'api_security_2fa_activate',
    ];

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        $user = $this->security->getUser();
        if ($user instanceof User && $user->isIncompleteFlow()) {
            if (!in_array($request->get('_route'), self::GRANTED_ROUTES)) {
                $event->setResponse(new JsonResponse(['master' => 'You must update your master password'], Response::HTTP_UNAUTHORIZED));
            }
        }
    }
}
