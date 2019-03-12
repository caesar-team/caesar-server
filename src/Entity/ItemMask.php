<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class ItemMask
 * @ORM\Entity
 */
class ItemMask
{
    use TimestampableEntity;

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    protected $secret;

    /**
     * @var Item
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="itemMasks", cascade={"persist"})
     * @ORM\JoinColumn(name="item_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    protected $item;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="itemMasks")
     * @ORM\JoinColumn(name="recipient_id", nullable=false)
     */
    protected $recipient;

    /**
     * @var Item|null
     * @ORM\OneToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="assembled_item_id", referencedColumnName="id")
     */
    protected $assembledItem;

    /**
     * ItemMask constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param null|string $secret
     */
    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @return User
     */
    public function getRecipient(): User
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     */
    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return Item|null
     */
    public function getAssembledItem(): ?Item
    {
        return $this->assembledItem;
    }

    /**
     * @param Item|null $assembledItem
     */
    public function setAssembledItem(?Item $assembledItem): void
    {
        $this->assembledItem = $assembledItem;
    }
}