<?php

declare(strict_types=1);

namespace App\Security\Trusted;

use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class TrustedDeviceManager implements TrustedDeviceManagerInterface
{
    private const DEFAULT_TOKEN_VERSION = 0;

    /**
     * @var TrustedDeviceTokenStorage
     */
    private $trustedTokenStorage;

    public function __construct(TrustedDeviceTokenStorage $trustedTokenStorage)
    {
        $this->trustedTokenStorage = $trustedTokenStorage;
    }

    public function addTrustedDevice($user, string $firewallName): void
    {
        if (!($user instanceof UserInterface)) {
            return;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);
        $this->trustedTokenStorage->addTrustedToken($username, $firewallName, $version);
    }

    public function isTrustedDevice($user, string $firewallName): bool
    {
        if (!($user instanceof UserInterface)) {
            return false;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);

        return $this->trustedTokenStorage->hasTrustedToken($username, $firewallName, $version);
    }

    private function getTrustedTokenVersion($user): int
    {
        if ($user instanceof TrustedDeviceInterface) {
            return $user->getTrustedTokenVersion();
        }

        return self::DEFAULT_TOKEN_VERSION;
    }
}
