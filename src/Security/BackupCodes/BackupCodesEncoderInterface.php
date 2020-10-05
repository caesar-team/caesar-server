<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

interface BackupCodesEncoderInterface
{
    public function encode(array $keys): array;

    public function isCodeValid(string $code, array $encodeKeys = []): bool;
}
