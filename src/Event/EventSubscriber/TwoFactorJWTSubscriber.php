<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintManager;
use App\Security\Fingerprint\FingerprintStasher;
use App\Security\Voter\TwoFactorInProgressVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TwoFactorJWTSubscriber implements EventSubscriberInterface
{
    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;

    /**
     * @var FingerprintStasher
     */
    private $fingerprintStasher;

    public function __construct(FingerprintManager $fingerprintManager, FingerprintStasher $fingerprintStasher)
    {
        $this->fingerprintManager = $fingerprintManager;
        $this->fingerprintStasher = $fingerprintStasher;
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
        if (!$user instanceof User) {
            return;
        }

        if ($user->isGoogleAuthenticatorEnabled()) {
            $fingerprint = $this->fingerprintStasher->unstash();

            if (empty($fingerprint) || !$this->fingerprintManager->isHasFingerprint($user, $fingerprint)) {
                $event->setData(array_merge($event->getData(), [TwoFactorInProgressVoter::CHECK_KEY_NAME => true]));
            }
        }
    }
}
