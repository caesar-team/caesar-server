<?php

declare(strict_types=1);

namespace App\Security\TwoFactor;

use App\Entity\User;
use App\Utils\HashidsEncoderInterface;
use Hashids\Hashids;

/**
 * @deprecated
 */
class BackUpCodesManager implements HashidsEncoderInterface
{
    public const CODES_COUNT = 20;
    private const DEFAULT_HASH_LENGTH = 10;

    public static function generate(User $user): void
    {
        $encoder = self::initEncoder();

        if ($user->hasBackupCodes()) {
            return;
        }

        $codes = [];
        for ($i = 0; self::CODES_COUNT > $i; ++$i) {
            $codes[] = $encoder->encode(random_int(100000, 999999)); //secured random six-digit number
        }

        $user->setBackupCodes($codes);
    }

    public static function initEncoder(): Hashids
    {
        $minHashLength = (int) getenv('BACKUP_CODE_HASH_LENGTH');
        if ($minHashLength <= 0) {
            $minHashLength = self::DEFAULT_HASH_LENGTH;
        }

        return new Hashids((string) getenv('BACKUP_CODE_SALT'), $minHashLength);
    }
}
