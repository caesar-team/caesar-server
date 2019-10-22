<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Plan
 * @ORM\Entity(repositoryClass="App\Repository\PlanRepository")
 */
class Plan
{
    public const FIELD_USERS_LIMIT = 'usersLimit';
    public const FIELD_ITEMS_LIMIT = 'itemsLimit';
    public const FIELD_MEMORY_LIMIT = 'memoryLimit';
    public const FIELD_TEAMS_LIMIT = 'teamsLimit';
    public const SUBSCRIPTION_INTERVAL_MONTH = '1';

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default"="unlimited"})
     */
    private $name = 'unlimited';

    /**
     * @ORM\Column(type="integer", options={"default":-1})
     * @var int
     */
    private $usersLimit = -1;

    /**
     * @ORM\Column(type="integer", options={"default":-1})
     * @var int
     */
    private $itemsLimit = -1;

    /**
     * @ORM\Column(type="integer", options={"default":-1})
     * @var int
     */
    private $teamsLimit = -1;

    /**
     * @ORM\Column(type="integer", length=255, options={"default":-1})
     * @var int
     */
    private $memoryLimit = -1;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $active = false;

    /**
     * @var int
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $subscriptionId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userSubscriptionId;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $subscribedAt;

    /**
     * @param string $label
     * @throws \Exception
     */
    public function __construct(string $label = null)
    {
        $this->id = Uuid::uuid4();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUsersLimit(): int
    {
        return $this->usersLimit;
    }

    public function setUsersLimit(int $usersLimit): void
    {
        $this->usersLimit = $usersLimit;
    }

    public function getItemsLimit(): int
    {
        return $this->itemsLimit;
    }

    public function setItemsLimit(int $itemsLimit): void
    {
        $this->itemsLimit = $itemsLimit;
    }

    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }

    public function setMemoryLimit(int $memoryLimit): void
    {
        $this->memoryLimit = $memoryLimit;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getTeamsLimit(): int
    {
        return $this->teamsLimit;
    }

    public function setTeamsLimit(int $teamsLimit): void
    {
        $this->teamsLimit = $teamsLimit;
    }

    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(int $subscriptionId): void
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function getUserSubscriptionId(): string
    {
        return $this->userSubscriptionId;
    }

    public function setUserSubscriptionId(string $userSubscriptionId): void
    {
        $this->userSubscriptionId = $userSubscriptionId;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(?\DateTimeImmutable $subscribedAt): void
    {
        $this->subscribedAt = $subscribedAt;
    }
}