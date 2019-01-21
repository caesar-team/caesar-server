<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Security\Request\RequestVerifierInterface;
use App\Security\Voter\TwoFactorInProgressVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class TwoFactorCheckSubscriber implements EventSubscriberInterface
{
    private const SECURED_ROUTE = '/api';
    private const TWO_FACTOR_ROUTE = '/api/2fa';

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
            KernelEvents::REQUEST => 'checkTwoFactor',
        ];
    }

    public function checkTwoFactor(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (false === strpos($request->getRequestUri(), self::SECURED_ROUTE) ||
            false !== strpos($request->getRequestUri(), self::TWO_FACTOR_ROUTE)
        ) {
            return;
        }

        $user = $this->security->getUser();
        if ($user instanceof User && !$user->isGoogleAuthenticatorEnabled()) {
            $event->setResponse(new JsonResponse([TwoFactorInProgressVoter::CHECK_KEY_NAME => TwoFactorInProgressVoter::FLAG_DISABLED], Response::HTTP_UNAUTHORIZED));
        }
    }
}
