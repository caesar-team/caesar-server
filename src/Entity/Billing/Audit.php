<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\DBAL\Types\Enum\BillingEnumType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="project_usage")
 * @ORM\Entity(repositoryClass="App\Repository\AuditRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Audit
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $usersCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $teamsCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private $itemsCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0}, length=255)
     */
    private $memoryUsed = 0;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @var Plan
     * @ORM\ManyToOne(targetEntity="Plan")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $billingPlan;

    /**
     * @param string $label
     * @throws \Exception
     */
    public function __construct(string $label = null)
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUsersCount(): int
    {
        return $this->usersCount;
    }

    public function setUsersCount(int $usersCount): void
    {
        $this->usersCount = $usersCount;
    }

    public function getTeamsCount(): int
    {
        return $this->teamsCount;
    }

    public function setTeamsCount(int $teamsCount): void
    {
        $this->teamsCount = $teamsCount;
    }

    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }

    public function setItemsCount(int $itemsCount): void
    {
        $this->itemsCount = $itemsCount;
    }

    public function getMemoryUsed(): int
    {
        return $this->memoryUsed;
    }

    public function setMemoryUsed(int $memoryUsed): void
    {
        $this->memoryUsed = $memoryUsed;
    }

    public function getBillingType(): string
    {
        return $this->billingPlan->getName();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function refreshUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getBillingPlan(): Plan
    {
        return $this->billingPlan;
    }

    public function setBillingPlan(Plan $billingPlan): void
    {
        $this->billingPlan = $billingPlan;
    }

    public function increaseItemsCount()
    {
        $this->itemsCount++;
    }

    public function decreaseItemsCount()
    {
        $this->itemsCount--;
    }

    public function increaseUsersCount()
    {
        $this->usersCount++;
    }

    public function decreaseUsersCount()
    {
        $this->usersCount--;
    }

    public function increaseMemoryUsage(int $bytes)
    {
        $this->memoryUsed = $this->memoryUsed + $bytes;
    }

    public function decreaseMemoryUsage(int $bytes)
    {
        $this->memoryUsed = $this->memoryUsed - $bytes;
    }
}