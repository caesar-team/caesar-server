<?php

declare(strict_types=1);

namespace App\Request\Srp;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class LoginPrepareRequest
{
    private ?User $user;

    /**
     * @Assert\NotBlank()
     */
    private ?string $publicEphemeralValue;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getPublicEphemeralValue(): ?string
    {
        return $this->publicEphemeralValue;
    }

    public function setPublicEphemeralValue(?string $publicEphemeralValue): void
    {
        $this->publicEphemeralValue = $publicEphemeralValue;
    }
}
