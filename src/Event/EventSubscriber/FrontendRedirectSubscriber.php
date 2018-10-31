<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Security\FrontendUriHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FrontendRedirectSubscriber implements EventSubscriberInterface
{
    private const OAUTH_ROUTE_NAME = 'hwi_oauth_service_redirect';

    /** @var string */
    private $frontendUri;

    /** @var FrontendUriHandler */
    private $frontendUriHandler;

    public function __construct(FrontendUriHandler $frontendUriHandler)
    {
        $this->frontendUriHandler = $frontendUriHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (self::OAUTH_ROUTE_NAME === $request->get('_route')) {
            $uri = $request->query->get('redirect_uri');
            $this->frontendUriHandler->validateUri($uri);
            $this->frontendUri = $uri;
            $request->getSession()->set('current_frontend_uri', $uri);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null !== $this->frontendUri) {
            $this->frontendUriHandler->persistUri($event->getResponse(), $this->frontendUri);
        }
    }
}
