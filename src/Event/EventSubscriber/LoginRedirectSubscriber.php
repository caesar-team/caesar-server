<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class LoginRedirectSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $storage;

    private RouterInterface $router;

    public function __construct(TokenStorageInterface $storage, RouterInterface $router)
    {
        $this->storage = $storage;
        $this->router = $router;
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

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ('hwi_oauth_connect' === $request->get('_route')) {
            $token = $this->storage->getToken();

            if ($token instanceof TwoFactorTokenInterface) {
                $event->setResponse(new RedirectResponse($this->router->generate('2fa_login')));
            }

            if ($token instanceof PostAuthenticationGuardToken) {
                $event->setResponse(new RedirectResponse($this->router->generate('easyadmin')));
            }
        }
    }
}
