<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateInvitedUserRequest
{
    /**
     * @var string
     *
     * @Assert\Email
     * @Assert\NotBlank
     * @AppAssert\UniqueEntityProperty(
     *     entityClass="App\Entity\User",
     *     field="email",
     *     repositoryMethod="findWithoutPublicKey",
     *     message="app.exception.user_already_exists",
     *     lowercase=true
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $encryptedPrivateKey;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $publicKey;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $seed;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $verifier;

    /**
     * @var string[]
     */
    private array $roles;

    public function __construct()
    {
        $this->roles = [];
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getEncryptedPrivateKey(): ?string
    {
        return $this->encryptedPrivateKey;
    }

    public function setEncryptedPrivateKey(string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getSeed(): ?string
    {
        return $this->seed;
    }

    public function setSeed(string $seed): void
    {
        $this->seed = $seed;
    }

    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(string $verifier): void
    {
        $this->verifier = $verifier;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
