<?php

namespace App\Tests\Security\BackupCodes;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\BackupCodes\BackupCodeManager;
use App\Security\BackupCodes\BackupCodesEncoderInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class BackupCodeManagerTest extends Unit
{
    private const DEFAULT_CODE = 'code';
    private const ENCODE = 'encode-code';

    /**
     * @var BackupCodesEncoderInterface|MockObject
     */
    private $encoder;

    /**
     * @var UserRepository|MockObject
     */
    private $repository;

    private BackupCodeManager $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->encoder = $this->createMock(BackupCodesEncoderInterface::class);
        $this->repository = $this->createMock(UserRepository::class);
        $this->service = new BackupCodeManager($this->encoder, $this->repository);
    }

    /** @test */
    public function testIsBackupCodeByUser()
    {
        $user = $this->createMock(BackupCodeInterface::class);

        $this->encoder->expects(self::once())->method('encode')->with([self::DEFAULT_CODE])->willReturn([self::ENCODE]);
        $user->expects(self::once())->method('isBackupCode')->with(self::ENCODE)->willReturn(true);

        self::assertTrue($this->service->isBackupCode($user, self::DEFAULT_CODE));
    }

    /** @test */
    public function testIsBackupCodeByOtherObject()
    {
        $user = $this->createMock(UserInterface::class);

        $this->encoder->expects(self::never())->method('encode');

        self::assertFalse($this->service->isBackupCode($user, self::DEFAULT_CODE));
    }

    /** @test */
    public function testInvalidateBackupCode()
    {
        $user = $this->createMock(User::class);

        $this->encoder->expects(self::once())->method('encode')->with([self::DEFAULT_CODE])->willReturn([self::ENCODE]);
        $this->repository->expects(self::once())->method('save')->with($user);
        $user->expects(self::once())->method('invalidateBackupCode')->with(self::ENCODE);

        $this->service->invalidateBackupCode($user, self::DEFAULT_CODE);
    }
}
