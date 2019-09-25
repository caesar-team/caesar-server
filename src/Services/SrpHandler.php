<?php

declare(strict_types=1);

namespace App\Services;

use Math_BigInteger;

class SrpHandler
{
    private const RAND_LENGTH = 128;
    private const HASH_ALGORITHM = 'sha256';
    private const N_BASE64 = 'dadfccb918e5f651d7a1b851efab43f2c17068c69013e37033347e8da75ca8d8370c26c4fbf1a4aaa4afd9b5ab32343749ee4fbf6fa279856fd7c3ade30ecf2b';
    private const G = '2';

    private $k;

    public function __construct()
    {
        $this->k = $this->hash(self::N_BASE64.self::G);
    }

    public function generatePublicServerEphemeral(string $privateServerEphemeral, string $verifier): string
    {
        $n = $this->base2dec(self::N_BASE64);
        $verifier = $this->base2dec($verifier);
        $privateServerEphemeral = $this->base2dec($privateServerEphemeral);
        $k = $this->base2dec($this->k);

        return $this->dec2base(bcadd(bcmul($k, $verifier), bcpowmod(self::G, $privateServerEphemeral, $n)));
    }

    public function generateSessionServer(string $publicClientEphemeral, string $publicServerEphemeral, string $privateServerEphemeral, string $verifier): string
    {
        $u = $this->base2dec($this->generateU($publicClientEphemeral, $publicServerEphemeral));
        $n = $this->base2dec(self::N_BASE64);
        $publicClientEphemeral = $this->base2dec($publicClientEphemeral);
        $verifier = $this->base2dec($verifier);
        $privateServerEphemeral = $this->base2dec($privateServerEphemeral);

        $S = $this->dec2base(bcpowmod(bcmul($publicClientEphemeral, bcpowmod($verifier, $u, $n)), $privateServerEphemeral, $n));

        return $S;
    }

    public function getRandomSeed($length = self::RAND_LENGTH): string
    {
        srand();
        $result = bin2hex(random_bytes($length));
        while (strlen($result) < $length) {
            $result = $result.$this->dec2base(rand());
        }
        $result = substr($result, 0, $length);

        return $result;
    }

    public function generateToken(): string
    {
        return $this->getRandomSeed(64);
    }

    public function generateFirstMatcher(string $publicClientEphemeral, string $publiceServerEphemeral, string $session): string
    {
        return $this->hash($publicClientEphemeral.$publiceServerEphemeral.$session);
    }

    public function generateSecondMatcher(string $publicClienEphemeral, string $firstMatcher, string $session): string
    {
        return $this->hash($publicClienEphemeral.$firstMatcher.$session);
    }

    public function generateSessionKey(string $session)
    {
        return $this->hash($session);
    }

    protected function generateU(string $publicClientEphemeral, string $publicServerEphemeral): string
    {
        return $this->hash($publicClientEphemeral.$publicServerEphemeral);
    }

    public function hash(string $value): string
    {
        return hash(static::HASH_ALGORITHM, hash(static::HASH_ALGORITHM, $value));
    }

    protected function dec2base($dec, $base = 16, $digits = false)
    {
        if ($base < 2 or $base > 256) {
            die('Invalid Base: '.$base);
        }
        bcscale(0);
        $value = '';
        if (!$digits) {
            $digits = $this->digits($base);
        }
        while ($dec > $base - 1) {
            $rest = bcmod((string) $dec, (string) $base);
            $dec = bcdiv((string) $dec, (string) $base);
            $value = $digits[$rest].$value;
        }
        $value = $digits[intval($dec)].$value;

        return (string) $value;
    }

    protected function base2dec(string $value, int $base = 16, bool $digits = false)
    {
        if ($base < 2 or $base > 256) {
            throw new \LogicException('Invalid Base: '.$base);
        }

        bcscale(0);
        if ($base < 37) {
            $value = strtolower($value);
        }
        if (!$digits) {
            $digits = $this->digits($base);
        }
        $size = strlen($value);
        $dec = '0';
        for ($loop = 0; $loop < $size; ++$loop) {
            $element = strpos($digits, $value[$loop]);
            $power = bcpow((string) $base, (string) ($size - $loop - 1));
            $dec = bcadd($dec, bcmul((string) $element, $power));
        }

        return (string) $dec;
    }

    protected function digits(int $base): string
    {
        if ($base > 64) {
            $digits = '';
            for ($loop = 0; $loop < 256; ++$loop) {
                $digits .= chr($loop);
            }
        } else {
            $digits = '0123456789abcdefghijklmnopqrstuvwxyz';
            $digits .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        }
        $digits = substr($digits, 0, $base);

        return (string) $digits;
    }

    public function generateX($seed, $username, $password)
    {
        $seed = new Math_BigInteger($seed, 16);
        $seed = $seed->toString();

        return $this->hash($seed.$this->hash($username.':'.$password));
    }

    public function generateVerifier($x)
    {
        $g = new Math_BigInteger(self::G, 10);
        $n = new Math_BigInteger(self::N_BASE64, 16);
        $x = new Math_BigInteger($x, 16);

        return $g->powMod($x, $n)->toHex();
    }
}
