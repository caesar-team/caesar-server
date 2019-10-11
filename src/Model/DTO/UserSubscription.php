<?php

declare(strict_types=1);

namespace App\Model\DTO;

use Caesar\Entity\UserSubscriptionInterface;

class UserSubscription implements UserSubscriptionInterface
{
    /**
     * @var string|null
     */
    private $status;
    /**
     * @var int|null
     */
    private $created;
    /**
     * @var string|null
     */
    private $subscriptionId;
    /**
     * @var User
     */
    private $user;
    /**
     * @var int
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getExternalSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function setExternalSubscriptionId(?string $externalSubscriptionId): void
    {
        $this->subscriptionId = $externalSubscriptionId;
    }

    public function getCreated(): ?int
    {
        return $this->created;
    }

    public function setCreated(?int $created): void
    {
        $this->created = $created;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}