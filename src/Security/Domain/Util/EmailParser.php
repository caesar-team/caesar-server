<?php

declare(strict_types=1);

namespace App\Security\Domain\Util;

final class EmailParser
{
    public static function getEmailDomain(string $email): ?string
    {
        if (preg_match('/(?<=@)(.+)$/', $email, $matches)) {
            return mb_strtolower($matches[1]);
        }

        return null;
    }
}
