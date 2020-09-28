<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Security\Fingerprint\Extractor\SessionExtractor;
use App\Security\Fingerprint\FingerprintExtractorInterface;
use App\Security\FrontendUriHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FrontendRedirectSubscriber implements EventSubscriberInterface
{
    private const OAUTH_ROUTE_NAME = 'hwi_oauth_service_redirect';

    private ?string $frontendUri = null;

    private FrontendUriHandler $frontendUriHandler;

    private FingerprintExtractorInterface $extractor;

    public function __construct(FrontendUriHandler $frontendUriHandler, FingerprintExtractorInterface $extractor)
    {
        $this->frontendUriHandler = $frontendUriHandler;
        $this->extractor = $extractor;
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

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (self::OAUTH_ROUTE_NAME === $request->get('_route')) {
            $uri = $request->query->get('redirect_uri');
            $this->frontendUriHandler->validateUri($uri);
            $this->frontendUri = $uri;
            $request->getSession()->set('current_frontend_uri', $uri); //Need redirect back to frontend if auth fails
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (null !== $this->frontendUri) {
            $this->frontendUriHandler->persistUri($event->getResponse(), $this->frontendUri);
        }

        $request = $event->getRequest();
        $fingerprint = $this->extractor->extract($request);
        if (null === $fingerprint) {
            return;
        }
        $request->getSession()->set(SessionExtractor::NAME_PARAM, $fingerprint);
    }
}
