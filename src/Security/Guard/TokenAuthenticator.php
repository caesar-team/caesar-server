<?php

declare(strict_types=1);

namespace App\Security\Guard;

use App\Entity\User;
use App\Security\TokenUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    public const DEFAULT_LIFETIME = 18000; //5 min

    /**
     * @var TokenExtractor
     */
    private $tokenExtractor;

    /**
     * @var TokenUserProvider
     */
    private $tokenUserProvider;

    /**
     * @var int
     */
    private $tokenLifeTime;

    /**
     * TokenAuthenticator constructor.
     * $tokenLifeTime: lifetime in seconds.
     *
     * @param TokenExtractor $tokenExtractor
     * @param int            $tokenLifeTime
     */
    public function __construct(TokenExtractor $tokenExtractor, int $tokenLifeTime = self::DEFAULT_LIFETIME)
    {
        $this->tokenExtractor = $tokenExtractor;
        $this->tokenLifeTime = $tokenLifeTime;
    }

    public function getCredentials(Request $request): ?string
    {
        return $this->tokenExtractor->extract($request);
    }

    public function getUser($preAuthToken, UserProviderInterface $userProvider): ?User
    {
        if ($userProvider instanceof TokenUserProvider) {
            $user = $userProvider->loadUserByToken($preAuthToken);
        } else {
            $user = $this->tokenUserProvider->loadUserByUsername($preAuthToken);
        }
        if (null === $user) {
            throw new UsernameNotFoundException('User not found');
        }

        $this->refreshTokenExpiration($user);

        return $user;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response();
    }

    public function supports(Request $request): bool
    {
        return false !== $this->tokenExtractor->extract($request);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $code = Response::HTTP_UNAUTHORIZED;

        return new JsonResponse(
            [
                'code' => $code,
                'message' => $exception->getMessage(),
            ],
            $code
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    private function refreshTokenExpiration(User $user)
    {
        if (null === $user->getTokenUpdated()) {
            $user->setCredentialNonExpired(false);

            return;
        }

        $expirationDate = $user->getTokenUpdated()->modify("+ {$this->tokenLifeTime} seconds");

        if ($expirationDate < new \DateTime()) {
            $user->setCredentialNonExpired(false);
        }
    }
}
