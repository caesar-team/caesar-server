<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use Swagger\Annotations as SWG;

class SharedChildItemView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $originalItemId;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $teamId;

    /**
     * @SWG\Property(type="string")
     */
    private \DateTime $lastUpdated;

    /**
     * @SWG\Property(type="string", enum=AccessEnumType::AVAILABLE_TYPES)
     */
    private string $access;

    /**
     * @SWG\Property(type="string", example="user@mail.com")
     */
    private string $email;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $link;

    /**
     * @SWG\Property(type="boolean")
     */
    private bool $isAccepted;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $publicKey;

    public function __construct()
    {
        $this->isAccepted = false;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getOriginalItemId(): string
    {
        return $this->originalItemId;
    }

    public function setOriginalItemId(string $originalItemId): void
    {
        $this->originalItemId = $originalItemId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    public function setTeamId(?string $teamId): void
    {
        $this->teamId = $teamId;
    }

    public function getLastUpdated(): \DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTime $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
    }

    public function setIsAccepted(bool $isAccepted): void
    {
        $this->isAccepted = $isAccepted;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }
}
