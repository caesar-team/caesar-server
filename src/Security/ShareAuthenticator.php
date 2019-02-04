<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Share;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ShareAuthenticator extends AbstractGuardAuthenticator
{
    private const ROUTE_NAME = 'api_share_get';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['Your access was revoked.'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return self::ROUTE_NAME === $request->attributes->get('_route');
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        return [
            'share' => $request->get('id'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $share = $this->entityManager->getRepository(Share::class)->findOneBy(['id' => $credentials['share']]);

        return $share ? $share->getUser() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $share = $this->entityManager->getRepository(Share::class)->findOneBy(['id' => $credentials['share']]);

        return null !== $share;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['Your access was revoked.'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
