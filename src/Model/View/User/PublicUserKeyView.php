<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class PublicUserKeyView
{
    /**
     * @SWG\Property(type="string", example="553d9b8d-fce0-4a53-8cba-f7d334160bc4")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string", example="asdfassdaaw46t4wesdra34w56")
     */
    private ?string $publicKey;

    /**
     * @SWG\Property(type="string", example="email@email")
     */
    private string $email;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
