<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="message_log", indexes={@ORM\Index(name="message_log_recipient_idx", columns={"recipient"})})
 * @ORM\Entity
 */
class MessageLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(length=50)
     */
    private string $event;

    /**
     * @ORM\Column
     */
    private string $recipient;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $deferred;

    /**
     * @ORM\Column(type="array")
     */
    private array $options;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $sentAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->deferred = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->sentAt = null;
        $this->options = [];
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    public function setDeferred(bool $deferred): void
    {
        $this->deferred = $deferred;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): void
    {
        $this->sentAt = $sentAt;
    }
}
