<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OAuthFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $url = $request->getSession()->get('current_frontend_uri', '/');
        if (empty($url)) {
            $url = '/';
        }

        return new RedirectResponse($url);
    }
}
