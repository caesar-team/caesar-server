<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

interface BackupCodesGeneratorInterface
{
    public const CODES_COUNT = 20;

    public function generate(int $size = self::CODES_COUNT): array;
}
