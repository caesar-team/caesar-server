<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;

class SrpHandlerTest extends TestCase
{
    /**
     * @var SrpTestHelper
     */
    private $srpTestHelper;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->srpTestHelper = new SrpTestHelper();
    }

    /**
     * @throws \Exception
     */
    public function testGeneratePublicServerEphemeral(): void
    {
        $publicValue = $this->srpTestHelper->getPublicServerEphemeral();

        $this->assertIsString($publicValue);
    }

    public function testGenerateSessionServer(): void
    {
        $serverSession = $this->srpTestHelper->getSessionServer();

        $this->assertIsString($serverSession);
    }

    public function testGetRandomSeed(): void
    {
        $randomSeeds = [];
        $randomSeeds[] = $this->srpTestHelper->getSrpHandler()->getRandomSeed();
        $randomSeeds[] = $this->srpTestHelper->getSrpHandler()->getRandomSeed();
        $randomSeeds[] = $this->srpTestHelper->getSrpHandler()->getRandomSeed();
        $seedsCount = count($randomSeeds);
        $filteredSeedsCount = array_unique($randomSeeds);
        $this->assertEquals($seedsCount, count($filteredSeedsCount));
    }

    public function testGenerateFirstMatcher(): void
    {
        $firstMatcher = $this->srpTestHelper->getFirstMatcher();

        $this->assertIsString($firstMatcher);
    }

    public function testGenerateSecondMatcher(): void
    {
        $srp = $this->srpTestHelper->getUser()->getSrp();
        $secondMatcher = $this->srpTestHelper->getSrpHandler()->generateSecondMatcher(
            $srp->getPublicClientEphemeralValue(),
            $this->srpTestHelper->getFirstMatcher(),
            $this->srpTestHelper->getSessionServer()
        );

        $this->assertIsString($secondMatcher);
    }
}