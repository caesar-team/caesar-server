<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="shares")
 * @ORM\HasLifecycleCallbacks
 */
class Share
{
    use TimestampableEntity;

    const STATUS_WAITING = 'WAITING';
    const STATUS_ACCEPTED = 'ACCEPTED';

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shares", cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", nullable=false, onDelete="CASCADE")
     */
    private $owner;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="availableShares", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var ShareItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="ShareItem", mappedBy="share", cascade={"persist"}, orphanRemoval=true)
     */
    private $sharedItems;

    /**
     * @var string|null
     * @ORM\Column(nullable=true, type="string", length=510)
     */
    private $link;

    /**
     * @var string
     * @ORM\Column(nullable=false, type="string", length=20, options={"default"="WAITING"})
     */
    private $status = self::STATUS_WAITING;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->sharedItems = new ArrayCollection();
        $this->status = self::STATUS_WAITING;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|UserInterface $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return ShareItem[]|Collection
     */
    public function getSharedItems(): Collection
    {
        return $this->sharedItems;
    }

    public function addSharedItem(ShareItem $shareItem): void
    {
        if (!$this->sharedItems->contains($shareItem)) {
            $this->sharedItems->add($shareItem);
            $shareItem->setShare($this);
        }
    }

    public function removeSharedItem(ShareItem $shareItem): void
    {
        $this->sharedItems->removeElement($shareItem);
    }

    /**
     * @param ShareItem[]|Collection $sharedItems
     */
    public function setSharedItems($sharedItems): void
    {
        $this->sharedItems = $sharedItems;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
