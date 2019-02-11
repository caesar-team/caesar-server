<?php

declare(strict_types=1);

namespace App\Model\DTO;

class ShareUser
{
    /**
     * @var string|null
     */
    private $login;
    /**
     * @var string|null
     */
    private $password;
    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $encryptedPrivateKey;

    /**
     * @var string|null
     */
    private $publicKey;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getEncryptedPrivateKey(): ?string
    {
        return $this->encryptedPrivateKey;
    }

    /**
     * @param null|string $encryptedPrivateKey
     */
    public function setEncryptedPrivateKey(?string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    /**
     * @return null|string
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param null|string $publicKey
     */
    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return null|string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param null|string $login
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
