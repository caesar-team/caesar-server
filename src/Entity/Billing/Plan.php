<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\DBAL\Types\Enum\BillingEnumType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Plan
 * @ORM\Entity
 */
class Plan
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="BillingEnumType", options={"default"="base"})
     */
    private $name = BillingEnumType::TYPE_BASE;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     * @var int
     */
    private $usersLimit = 0;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     * @var int
     */
    private $itemsLimit = 0;

    /**
     * @ORM\Column(type="integer", length=255, options={"default":0})
     * @var int
     */
    private $memoryLimit = 0;

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
}