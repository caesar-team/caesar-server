<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Request\LoginRequest;
use App\Model\Request\SessionMatcher;
use Doctrine\ORM\EntityManagerInterface;

class SrpUserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SrpHandler
     */
    private $srpHandler;

    public function __construct(SrpHandler $srpHandler, EntityManagerInterface $entityManager)
    {
        $this->srpHandler = $srpHandler;
        $this->entityManager = $entityManager;
    }

    public function getMatcherSession(LoginRequest $request): SessionMatcher
    {
        $user = $request->getUser();
        $srp = $user->getSrp();

        $serverSession = $this->srpHandler->generateSessionServer(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $srp->getPrivateServerEphemeralValue(),
            $srp->getVerifier()
        );

        $matcher = $this->srpHandler->generateFirstMatcher(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $serverSession
        );

        return new SessionMatcher($serverSession, $matcher);
    }

    public function generateSecondMatcher(LoginRequest $request, SessionMatcher $sessionMatcher): string
    {
        $serverSession = $sessionMatcher->getServerSession();
        $matcher = $sessionMatcher->getMatcher();

        return $this->srpHandler->generateSecondMatcher(
            $request->getUser()->getSrp()->getPublicClientEphemeralValue(),
            $matcher,
            $serverSession
        );
    }
}
