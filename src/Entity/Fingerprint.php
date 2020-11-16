<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FingerprintRepository")
 * @ORM\Table(indexes={@ORM\Index(name="idx_fingerprint_string", columns={"fingerprint"})})
 */
class Fingerprint
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
     * @ORM\Column(nullable=true)
     */
    private $client;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $lastIp;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $fingerprint;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $expiredAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fingerprints")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLastIp(): ?string
    {
        return $this->lastIp;
    }

    public function setLastIp(?string $lastIp): void
    {
        $this->lastIp = $lastIp;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): void
    {
        $this->client = $client;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiredAt(): \DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    public function isValidExpired(): bool
    {
        return $this->expiredAt >= new \DateTimeImmutable();
    }
}
