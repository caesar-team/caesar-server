<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Srp.
 *
 * @ORM\Table
 * @ORM\Entity
 */
class Srp
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $seed;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $verifier;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true);
     */
    private $publicClientEphemeralValue;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true);
     */
    private $publicServerEphemeralValue;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true);
     */
    private $privateServerEphemeralValue;

    /**
     * Srp constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSeed(): ?string
    {
        return $this->seed;
    }

    public function setSeed(string $seed)
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

    public function getPublicClientEphemeralValue(): ?string
    {
        return $this->publicClientEphemeralValue;
    }

    public function setPublicClientEphemeralValue(?string $value): void
    {
        $this->publicClientEphemeralValue = $value;
    }

    public function getPublicServerEphemeralValue(): ?string
    {
        return $this->publicServerEphemeralValue;
    }

    public function setPublicServerEphemeralValue(?string $publicServerEphemeralValue): void
    {
        $this->publicServerEphemeralValue = $publicServerEphemeralValue;
    }

    public function getPrivateServerEphemeralValue(): ?string
    {
        return $this->privateServerEphemeralValue;
    }

    public function setPrivateServerEphemeralValue(?string $privateServerEphemeralValue): void
    {
        $this->privateServerEphemeralValue = $privateServerEphemeralValue;
    }
}
