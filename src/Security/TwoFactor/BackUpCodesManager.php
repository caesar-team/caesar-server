<?php

declare(strict_types=1);


namespace App\Security\TwoFactor;

use App\Entity\User;
use Hashids\Hashids;

class BackUpCodesManager
{
    const CODES_COUNT = 20;

    static public function generate(User $user): void
    {
        $encoder = self::initEncoder();

        if ($user->hasBackupCodes()) {
            return;
        }

        $codes = [];
        for ($i = 0; self::CODES_COUNT > $i; $i++) {
            $codes[] = $encoder->encode(random_int(100000, 999999)); //secured random six-digit number
        }

        $user->setBackupCodes($codes);
    }

    static public function initEncoder()
    {
        return new Hashids(getenv('BACKUP_CODE_SALT'), getenv('BACKUP_CODE_HASH_LENGTH'));
    }
}