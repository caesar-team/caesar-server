<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Entity\User;
use App\Validator\Constraints as AppAssert;

final class TwoFactoryAuthEnableRequest
{
    private ?string $secret;

    private ?string $fingerprint;

    /**
     * @AppAssert\GoogleAuthenticatorCheckCode()
     */
    private ?string $authCode;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function setAuthCode(?string $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
