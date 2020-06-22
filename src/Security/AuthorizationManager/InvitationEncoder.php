<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

class InvitationEncoder
{
    /**
     * @var string
     */
    private $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    public static function initEncoder(): InvitationEncoder
    {
        return new self((string) getenv('INVITATION_SALT'));
    }

    public function encode(string $string): string
    {
        return $this->generateHash($string);
    }

    public function isHashEquals(string $knownString, string $userString): bool
    {
        return $this->encode($knownString) && $this->encode($userString);
    }

    private function generateHash(string $string): string
    {
        $ctx = hash_init('sha512');
        hash_update($ctx, $string);
        hash_update($ctx, $this->salt);

        return hash_final($ctx);
    }
}
