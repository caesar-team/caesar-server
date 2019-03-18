<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\ChildItemAwareInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class ItemMask
 * @ORM\Entity
 */
class ItemMask implements ChildItemAwareInterface
{
    use TimestampableEntity;

    const CAUSE_INVITE = 'invite';
    const CAUSE_SHARE = 'share';
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
    protected $originalItem;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="itemMasks")
     * @ORM\JoinColumn(name="recipient_id", nullable=false)
     */
    protected $recipient;

    /**
     * @var string
     *
     * @ORM\Column(type="AccessEnumType", nullable=false)
     */
    protected $access;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=510, nullable=true)
     */
    protected $link;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true, options={"default"="invite"})
     */
    protected $cause = self::CAUSE_INVITE;

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
    public function getOriginalItem(): Item
    {
        return $this->originalItem;
    }

    /**
     * @param Item $originalItem
     */
    public function setOriginalItem(Item $originalItem): void
    {
        $this->originalItem = $originalItem;
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
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    /**
     * @return null|string
     */
    public function getCause(): ?string
    {
        return $this->cause;
    }

    /**
     * @param null|string $cause
     */
    public function setCause(?string $cause): void
    {
        $this->cause = $cause;
    }

    /**
     * @return null|string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param null|string $link
     */
    public function setLink(?string $link): void
    {
        $this->link = $link;
    }
}