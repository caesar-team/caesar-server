<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\SystemLimitRepository")
 */
class SystemLimit
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
     *
     * @ORM\Column(unique=true)
     */
    private $inspector;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"default": -1})
     */
    private $limitSize;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->inspector = '';
        $this->limitSize = -1;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getInspector(): string
    {
        return $this->inspector;
    }

    public function setInspector(string $inspector): void
    {
        $this->inspector = $inspector;
    }

    public function getLimitSize(): int
    {
        return $this->limitSize;
    }

    public function setLimitSize(int $limitSize): void
    {
        $this->limitSize = $limitSize;
    }

    public function addLimitSize(int $limitSize): void
    {
        // for unlimited size
        if (0 > $this->limitSize) {
            $this->limitSize = 0;
        }

        $this->limitSize += $limitSize;
    }

    public function isRestricted(int $used): bool
    {
        return $this->getLimitSize() < $used && !$this->isUnlimited();
    }

    public function isUnlimited(): bool
    {
        return -1 === $this->getLimitSize();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId()->toString(),
            'inspector' => $this->getInspector(),
            'limit' => $this->getLimitSize(),
        ];
    }
}
