<?php

declare(strict_types=1);

namespace App\Request\Srp;

use App\Request\SrpAwareRequestInterface;
use App\Validator\Constraints\UniqueEntityProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationRequest implements SrpAwareRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @UniqueEntityProperty(entityClass="App\Entity\User", field="email", message="user.email.unique")
     */
    private ?string $email;

    /**
     * @Assert\NotBlank()
     */
    private ?string $seed;

    /**
     * @Assert\NotBlank()
     */
    private ?string $verifier;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getSeed(): ?string
    {
        return $this->seed;
    }

    public function setSeed(?string $seed): void
    {
        $this->seed = $seed;
    }

    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(?string $verifier): void
    {
        $this->verifier = $verifier;
    }
}
