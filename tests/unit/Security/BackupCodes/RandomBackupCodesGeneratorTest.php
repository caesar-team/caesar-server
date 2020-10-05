<?php

namespace App\Tests\Security\BackupCodes;

use App\Security\BackupCodes\RandomBackupCodesGenerator;
use Codeception\Test\Unit;

final class RandomBackupCodesGeneratorTest extends Unit
{
    private RandomBackupCodesGenerator $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new RandomBackupCodesGenerator();
    }

    /**
     * @dataProvider sizes()
     */
    public function testGenerator(int $size)
    {
        $codes = $this->service->generate($size);
        self::assertGreaterThanOrEqual(RandomBackupCodesGenerator::MIN, $codes[0]);
        self::assertLessThanOrEqual(RandomBackupCodesGenerator::MAX, $codes[0]);
        self::assertCount($size, $codes);
    }

    public function sizes(): array
    {
        return [
            [10],
            [13],
        ];
    }

    public function testExceptionGenerator()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->service->generate(-1);
    }
}
