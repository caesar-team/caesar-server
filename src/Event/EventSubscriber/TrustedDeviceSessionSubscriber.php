<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Security\Trusted\TrustedDeviceTokenStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class TrustedDeviceSessionSubscriber implements EventSubscriberInterface
{
    /**
     * @var TrustedDeviceTokenStorage
     */
    private $trustedTokenStorage;

    public function __construct(TrustedDeviceTokenStorage $trustedTokenStorage)
    {
        $this->trustedTokenStorage = $trustedTokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if ($this->trustedTokenStorage->hasUpdatedSession()) {
            $this->trustedTokenStorage->updateTokenValue($this->trustedTokenStorage->getTokenValue());
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->query->has(TrustedDeviceTokenStorage::TRUSTED_DEVICE_QUERY_NAME)) {
            $this->trustedTokenStorage->updateTokenValue(
                $request->query->get(TrustedDeviceTokenStorage::TRUSTED_DEVICE_QUERY_NAME)
            );
        }
    }
}
