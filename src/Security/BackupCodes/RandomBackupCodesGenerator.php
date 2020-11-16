<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

class RandomBackupCodesGenerator implements BackupCodesGeneratorInterface
{
    public const MIN = 100000;
    public const MAX = 999999;

    public function generate(int $size = self::CODES_COUNT): array
    {
        if ($size < 1) {
            throw new \BadMethodCallException('Size could not less than 1');
        }

        $codes = [];
        for ($i = 0; $size > $i; ++$i) {
            $codes[] = (string) random_int(self::MIN, self::MAX);
        }

        return $codes;
    }
}
