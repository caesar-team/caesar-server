<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Services\SrpHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class TokenRedirectHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var SrpHandler
     */
    private $srpHandler;

    /**
     * @var FrontendUriHandler
     */
    private $frontendUriHandler;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * JwtRedirectHandler constructor.
     *
     * @param SrpHandler             $srpHandler
     * @param EntityManagerInterface $manager
     * @param FrontendUriHandler     $frontendUriHandler
     */
    public function __construct(SrpHandler $srpHandler, EntityManagerInterface $manager, FrontendUriHandler $frontendUriHandler)
    {
        $this->srpHandler = $srpHandler;
        $this->entityManager = $manager;
        $this->frontendUriHandler = $frontendUriHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        $token = $this->srpHandler->generateToken();
        $user->setToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $url = $this->generateFrontendUri($request, $token, $user);

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     * @param string  $token
     * @param User    $user
     *
     * @return string
     */
    private function generateFrontendUri(Request $request, string $token, User $user): string
    {
        $uri = $this->frontendUriHandler->extractUri($request);

        return \sprintf('%s?token=%s', $uri, $token);
    }
}
