<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Srp;
use App\Entity\User;
use App\Services\SrpHandler;
use PHPUnit\Framework\TestCase;

class SrpHandlerTest extends TestCase
{
    const VERIFIER = 'test';
    const PUBLIC_CLIENT_VALUE = 'test';
    /**
     * @var SrpHandler
     */
    private $srpHandler;
    /**
     * @var User
     */
    private $user;

    /**
     * @throws \Exception
     */
    public function setUp(): void
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
     * @throws \Exception
     */
    public function testGeneratePublicServerEphemeral(): void
    {
        $publicValue = $this->getPublicServerEphemeral();

        $this->assertIsString($publicValue);
    }

    public function testGenerateSessionServer(): void
    {
        $serverSession = $this->getSessionServer();

        $this->assertIsString($serverSession);
    }

    public function testGetRandomSeed(): void
    {
        $randomSeeds = [];
        $randomSeeds[] = $this->srpHandler->getRandomSeed();
        $randomSeeds[] = $this->srpHandler->getRandomSeed();
        $randomSeeds[] = $this->srpHandler->getRandomSeed();
        $seedsCount = count($randomSeeds);
        $filteredSeedsCount = array_unique($randomSeeds);
        $this->assertEquals($seedsCount, count($filteredSeedsCount));
    }

    public function testGenerateFirstMatcher(): void
    {
        $firstMatcher = $this->getFirstMatcher();

        $this->assertIsString($firstMatcher);
    }

    public function testGenerateSecondMatcher(): void
    {
        $srp = $this->user->getSrp();
        $secondMatcher = $this->srpHandler->generateSecondMatcher($srp->getPublicClientEphemeralValue(), $this->getFirstMatcher(), $this->getSessionServer());

        $this->assertIsString($secondMatcher);
    }

    private function getFirstMatcher(): string
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
    private function getPublicServerEphemeral(): string
    {
        $privateEphemeral = $this->user->getSrp()->getPrivateServerEphemeralValue();

        return $this->srpHandler->generatePublicServerEphemeral($privateEphemeral, $this->user->getSrp()->getVerifier());
    }

    private function getSessionServer(): string
    {
        $srp = $this->user->getSrp();
        return $this->srpHandler->generateSessionServer(
            $srp->getPublicClientEphemeralValue(),
            $srp->getPublicServerEphemeralValue(),
            $srp->getPrivateServerEphemeralValue(),
            $srp->getVerifier()
        );
    }
}