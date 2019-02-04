<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Security\Fingerprint\FingerprintStasher;
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

    /** @var string */
    private $fingerprint;

    /** @var FingerprintStasher */
    private $fingerprintStasher;

    public function __construct(FrontendUriHandler $frontendUriHandler, FingerprintStasher $fingerprintStasher)
    {
        $this->frontendUriHandler = $frontendUriHandler;
        $this->fingerprintStasher = $fingerprintStasher;
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
            $this->fingerprint = $request->query->get('fingerprint');
            $request->getSession()->set('current_frontend_uri', $uri); //Need redirect back to frontend if auth fails
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null !== $this->frontendUri) {
            $this->frontendUriHandler->persistUri($event->getResponse(), $this->frontendUri);
        }

        if (null !== $this->fingerprint) {
            $this->fingerprintStasher->stash($event->getResponse(), $this->fingerprint);
        }
    }
}
