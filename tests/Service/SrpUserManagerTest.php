<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Srp;
use App\Entity\User;
use App\Model\Request\LoginRequest;
use App\Model\Request\SessionMatcher;
use App\Services\SrpUserManager;
use PHPUnit\Framework\TestCase;

class SrpUserManagerTest extends TestCase
{
    /**
     * @var SrpUserManager
     */
    private $srpUserManager;
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
        $this->srpUserManager = new SrpUserManager($this->srpTestHelper->getSrpHandler());
    }

    /**
     * @throws \Exception
     */
    public function testGetMatcherSession(): void
    {
        $loginRequest = new LoginRequest();
        $loginRequest->setUser($this->srpTestHelper->getUser());
        $sessionMatcher = $this->srpUserManager->getMatcherSession($loginRequest);

        $this->assertInstanceOf(SessionMatcher::class, $sessionMatcher);
    }

    public function testGenerateSecondMatcher(): void
    {
        $loginRequest = new LoginRequest();
        $loginRequest->setUser($this->srpTestHelper->getUser());
        $sessionMatcher = $this->srpUserManager->getMatcherSession($loginRequest);
        $secondMatcher = $this->srpUserManager->generateSecondMatcher($loginRequest, $sessionMatcher);

        $this->assertIsString($secondMatcher);
    }
}