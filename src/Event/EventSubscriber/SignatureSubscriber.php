<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Security\Request\RequestVerifierInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class SignatureSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var RequestVerifierInterface
     */
    private $requestVerifier;

    public function __construct(Security $security, RequestVerifierInterface $requestVerifier)
    {
        $this->security = $security;
        $this->requestVerifier = $requestVerifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (('false' === getenv('AVAILABLE_REQUEST_SIGNATURE') && 'false' === getenv('STRICT_AUDIT_TRAIL'))
            || $this->requestVerifier->verifyRequest($request, $this->getPublicKey())
        ) {
            return;
        }

        throw new BadRequestHttpException('Request signature is not valid');
    }

    private function getPublicKey(): string
    {
        $strictAuditTrail = getenv('STRICT_AUDIT_TRAIL');
        $proxyPublicKey = getenv('PROXY_PUBLIC_KEY');
        if ('false' === $strictAuditTrail || !$proxyPublicKey) {
            return (string) $this->security->getUser()->getPublicKey();
        }

        return (string) $proxyPublicKey;
    }
}
