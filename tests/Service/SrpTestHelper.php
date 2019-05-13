<?php

declare(strict_types=1);

namespace App\Tests\Service;


use App\Entity\Srp;
use App\Entity\User;
use App\Services\SrpHandler;

class SrpTestHelper
{
    const VERIFIER = 'test';
    const PUBLIC_CLIENT_VALUE = 'test';

    /**
     * @var User
     */
    private $user;
    /**
     * @var SrpHandler
     */
    private $srpHandler;

    /**
     * SrpTestHelper constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->srpHandler = new SrpHandler();
        $srp = new Srp();
        $srp->setPublicClientEphemeralValue(self::PUBLIC_CLIENT_VALUE);
        $srp->setPrivateServerEphemeralValue($this->srpHandler->getRandomSeed());
        $srp->setVerifier(self::VERIFIER);
        $this->user = new User($srp);
        $this->user->getSrp()->setPublicServerEphemeralValue($this->getPublicServerEphemeral());
    }


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    public function getFirstMatcher(): string
    {
        $srp = $this->user->getSrp();
        return $this->srpHandler->generateFirstMatcher(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $this->getSessionServer()
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getPublicServerEphemeral(): string
    {
        $privateEphemeral = $this->user->getSrp()->getPrivateServerEphemeralValue();

        return $this->srpHandler->generatePublicServerEphemeral($privateEphemeral, $this->user->getSrp()->getVerifier());
    }

    public function getSessionServer(): string
    {
        $srp = $this->user->getSrp();
        return $this->srpHandler->generateSessionServer(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $srp->getPrivateServerEphemeralValue(),
            $srp->getVerifier()
        );
    }

    /**
     * @return SrpHandler
     */
    public function getSrpHandler(): SrpHandler
    {
        return $this->srpHandler;
    }
}