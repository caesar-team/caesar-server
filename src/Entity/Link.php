<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Link
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Item
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Item", inversedBy="link")
     */
    protected $parentItem;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     */
    protected $guestUser;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $data;

    public function __construct(User $guest, Item $parentItem)
    {
        $this->id = Uuid::uuid4();
        $this->guestUser = $guest;
        $this->parentItem = $parentItem;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getGuestUser(): User
    {
        return $this->guestUser;
    }

    public function getParentItem(): Item
    {
        return $this->parentItem;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }
}
