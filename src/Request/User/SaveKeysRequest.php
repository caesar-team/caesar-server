<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class SaveKeysRequest
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $encryptedPrivateKey;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $publicKey;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getEncryptedPrivateKey(): ?string
    {
        return $this->encryptedPrivateKey;
    }

    public function setEncryptedPrivateKey(?string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
