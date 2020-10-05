<?php

namespace App\Tests\Security\BackupCodes;

use App\Security\BackupCodes\NativeBackupCodesEncoder;
use Codeception\Test\Unit;

final class NativeBackupCodesEncoderTest extends Unit
{
    private const SALT = 'salt';
    private const LENGTH = 12;

    private const HASH_1111 = '6b1f007f2212';
    private const HASH_22222 = 'dd74143e3ab1';

    private NativeBackupCodesEncoder $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new NativeBackupCodesEncoder(self::SALT, self::LENGTH);
    }

    /**
     * @dataProvider codes
     */
    public function testEncode(array $codes, array $expectedCodes)
    {
        $encode = $this->service->encode($codes);

        self::assertNotEquals($encode, $codes);
        self::assertEquals($encode, $expectedCodes);
    }

    public function testIsCodeValid()
    {
        self::assertTrue($this->service->isCodeValid('1111', [self::HASH_1111, self::HASH_22222]));
        self::assertFalse($this->service->isCodeValid('11111', [self::HASH_1111, self::HASH_22222]));
    }

    public function codes(): array
    {
        return [
            [['1111'],  [self::HASH_1111]],
            [['1111', '22222'], [self::HASH_1111, self::HASH_22222]],
        ];
    }
}
