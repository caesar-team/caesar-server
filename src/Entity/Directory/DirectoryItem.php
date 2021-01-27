<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use App\Entity\Item;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class DirectoryItem
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="directoryItems", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Item $item;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory\AbstractDirectory", inversedBy="directoryItems", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private AbstractDirectory $directory;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }

    public function setDirectory(AbstractDirectory $directory): void
    {
        $this->directory = $directory;
    }
}
