<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

class NativeBackupCodesEncoder implements BackupCodesEncoderInterface
{
    private const DEFAULT_HASH_LENGTH = 10;

    private string $salt;
    private int $hashLength;

    public function __construct(string $salt, int $hashLength = self::DEFAULT_HASH_LENGTH)
    {
        $this->salt = $salt;
        $this->hashLength = $hashLength;
    }

    public function encode(array $keys): array
    {
        return array_map([$this, 'hash'], $keys);
    }

    public function isCodeValid(string $code, array $encodeKeys = []): bool
    {
        return in_array($this->hash($code), $encodeKeys);
    }

    private function hash(string $key): string
    {
        return substr(md5($this->salt.$key), 0, $this->hashLength);
    }
}
