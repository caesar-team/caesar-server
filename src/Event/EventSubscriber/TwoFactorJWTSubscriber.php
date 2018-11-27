<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Security\Voter\TwoFactorInProgressVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TwoFactorJWTSubscriber implements EventSubscriberInterface
{
    /**
     * @var TrustedDeviceManagerInterface
     */
    private $trustedDeviceManager;

    public function __construct(TrustedDeviceManagerInterface $trustedDeviceManager)
    {
        $this->trustedDeviceManager = $trustedDeviceManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_CREATED => 'onJWTCreated',
        ];
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        if ($user instanceof  User && $user->isGoogleAuthenticatorEnabled()) {
            if (!$this->trustedDeviceManager->isTrustedDevice($user, 'api')) {
                $event->setData(array_merge($event->getData(), [TwoFactorInProgressVoter::CHECK_KEY_NAME => true]));
            }
        }
    }
}
