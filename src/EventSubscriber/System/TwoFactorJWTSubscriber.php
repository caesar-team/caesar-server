<?php

declare(strict_types=1);

namespace App\EventSubscriber\System;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintCheckerInterface;
use App\Security\Voter\TwoFactorInProgressVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TwoFactorJWTSubscriber implements EventSubscriberInterface
{
    private FingerprintCheckerInterface $checker;

    public function __construct(FingerprintCheckerInterface $checker)
    {
        $this->checker = $checker;
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

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            return;
        }

        if ($this->checker->hasValidFingerprint($user)) {
            return;
        }

        $event->setData(array_merge($event->getData(), [TwoFactorInProgressVoter::CHECK_KEY_NAME => true]));
    }
}
