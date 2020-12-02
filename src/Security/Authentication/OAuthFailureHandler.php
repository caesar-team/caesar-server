<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OAuthFailureHandler implements AuthenticationFailureHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $session = $request->getSession();

        $url = $session->get('current_frontend_uri', null);
        if (null !== $url) {
            return new RedirectResponse(sprintf('%s?error=%s', $url, $exception->getMessage()));
        }

        $adminTarget = $session->get('_security.admin.target_path');
        if (null !== $adminTarget) {
            if ($session instanceof Session) {
                $session->getFlashBag()->add('danger', $exception->getMessage());
            }

            return new RedirectResponse(
                $this->router->generate('_login')
            );
        }

        return new RedirectResponse('/');
    }
}
