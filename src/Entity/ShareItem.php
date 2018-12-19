<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="share_item")
 */
class ShareItem
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="externalSharedItems", cascade={"persist"})
     * @ORM\JoinColumn(name="item_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $item;

    /**
     * @var Share
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Share", inversedBy="sharedItems")
     * @ORM\JoinColumn(name="share_id", nullable=false, onDelete="CASCADE")
     */
    private $share;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $secret;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getShare(): ?Share
    {
        return $this->share;
    }

    public function setShare(Share $share): void
    {
        $this->share = $share;
    }
}
