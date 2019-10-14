<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JwtRedirectHandler implements AuthenticationSuccessHandlerInterface
{
    /** @var JWTTokenManagerInterface */
    private $jwtTokenManager;
    /** @var FrontendUriHandler */
    private $frontendUriHandler;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * JwtRedirectHandler constructor.
     *
     * @param JWTTokenManagerInterface $jwtTokenManager
     * @param FrontendUriHandler $frontendUriHandler
     * @param RouterInterface $router
     */
    public function __construct(
        JWTTokenManagerInterface $jwtTokenManager,
        FrontendUriHandler $frontendUriHandler, RouterInterface $router
    )
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->frontendUriHandler = $frontendUriHandler;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        $jwt = $this->jwtTokenManager->create($user);
        $url = $this->generateFrontendUri($request, $jwt);

        $roles = [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN];
        if (!$url && $user->hasOneOfRoles($roles)) {
            $url = $this->router->generate('easyadmin');
        }

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     * @param string  $jwt
     * @param User    $user
     *
     * @return string
     */
    private function generateFrontendUri(Request $request, string $jwt): ?string
    {
        $uri = $this->frontendUriHandler->extractUri($request);

        return $uri ? \sprintf('%s?jwt=%s', $uri, $jwt) : null;
    }
}
