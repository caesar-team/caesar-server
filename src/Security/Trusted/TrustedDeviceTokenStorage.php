<?php

declare(strict_types=1);

namespace App\Security\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\JwtTokenEncoder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class TrustedDeviceTokenStorage
{
    public const TRUSTED_DEVICE_QUERY_NAME = '_trusted';
    private const TRUSTED_DEVICE_SESSION_NAME = '_trusted';
    private const TOKEN_DELIMITER = ';';

    /**
     * @var JwtTokenEncoder
     */
    private $jwtTokenEncoder;

    /**
     * @var int
     */
    private $trustedTokenLifetime;

    /**
     * @var TrustedDeviceToken[]
     */
    private $trustedTokenList;

    /**
     * @var bool
     */
    private $updateSession = false;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        SessionInterface $session,
        JwtTokenEncoder $jwtTokenEncoder,
        int $trustedTokenLifetime
    ) {
        $this->session = $session;
        $this->jwtTokenEncoder = $jwtTokenEncoder;
        $this->trustedTokenLifetime = $trustedTokenLifetime;
    }

    public function hasUpdatedSession(): bool
    {
        return $this->updateSession;
    }

    public function getTokenValue(): ?string
    {
        return implode(self::TOKEN_DELIMITER, array_map(function (TrustedDeviceToken $token) {
            return $token->serialize();
        }, $this->getTrustedTokenList()));
    }

    public function hasTrustedToken(string $username, string $firewall, int $version): bool
    {
        foreach ($this->getTrustedTokenList() as $key => $token) {
            if ($token->authenticatesRealm($username, $firewall)) {
                if ($token->versionMatches($version)) {
                    return true;
                } else {
                    // Remove the trusted token, because the version is outdated
                    unset($this->trustedTokenList[$key]);
                    $this->updateSession = true;
                }
            }
        }

        return false;
    }

    public function addTrustedToken(string $username, string $firewall, int $version): void
    {
        foreach ($this->getTrustedTokenList() as $key => $token) {
            if ($token->authenticatesRealm($username, $firewall)) {
                // Remove the trusted token, because it is to be replaced with a newer one
                unset($this->trustedTokenList[$key]);
            }
        }

        $validUntil = $this->getValidUntil();
        $jwtToken = $this->jwtTokenEncoder->generateToken($username, $firewall, $version, $validUntil);
        $this->trustedTokenList[] = new TrustedDeviceToken($jwtToken);
    }

    public function updateTokenValue(?string $value): void
    {
        $this->session->set(self::TRUSTED_DEVICE_SESSION_NAME, $value);
    }

    public function getExpiresAtToken(): int
    {
        return time() + $this->trustedTokenLifetime;
    }

    private function getValidUntil(): \DateTime
    {
        return $this->getDateTimeNow()->add(new \DateInterval('PT'.$this->trustedTokenLifetime.'S'));
    }

    private function getDateTimeNow(): \DateTime
    {
        return new \DateTime();
    }

    /**
     * @return TrustedDeviceToken[]
     */
    private function getTrustedTokenList(): array
    {
        if (null === $this->trustedTokenList) {
            $this->trustedTokenList = $this->readTrustedTokenList();
        }

        return $this->trustedTokenList;
    }

    /**
     * @return TrustedDeviceToken[]
     */
    private function readTrustedTokenList(): array
    {
        $cacheValue = $this->readCacheValue();
        if (!$cacheValue) {
            return [];
        }

        $trustedTokenList = [];
        $trustedTokenEncodedList = explode(self::TOKEN_DELIMITER, $cacheValue);
        foreach ($trustedTokenEncodedList as $trustedTokenEncoded) {
            $trustedToken = $this->jwtTokenEncoder->decodeToken($trustedTokenEncoded);
            if (!$trustedToken || $trustedToken->isExpired()) {
                $this->updateSession = true; // When there are invalid token, update the cookie to remove them
            } else {
                $trustedTokenList[] = new TrustedDeviceToken($trustedToken);
            }
        }

        return $trustedTokenList;
    }

    private function readCacheValue(): ?string
    {
        return $this->session->get(self::TRUSTED_DEVICE_SESSION_NAME);
    }
}
