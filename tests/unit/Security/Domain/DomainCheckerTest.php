<?php

namespace App\Tests\Security\Domain;

use App\Security\Domain\DomainChecker;
use App\Security\Domain\Repository\AllowedDomainRepositoryInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

final class DomainCheckerTest extends Unit
{
    /**
     * @var AllowedDomainRepositoryInterface|MockObject
     */
    private $repository;

    private DomainChecker $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(AllowedDomainRepositoryInterface::class);
        $this->service = new DomainChecker($this->repository);
    }

    /** @test */
    public function checkDomain()
    {
        $this->repository->method('getAllowedDomains')->willReturn([
            'test.com', 'example.com',
        ]);

        self::assertTrue($this->service->check('test@test.com'));
        self::assertTrue($this->service->check('test@example.com'));
        self::assertFalse($this->service->check('test@false.com'));
        self::assertFalse($this->service->check(''));
        self::assertFalse($this->service->check('test.com'));
    }
}
