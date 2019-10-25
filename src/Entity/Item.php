<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Utils\ChildItemAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 *  @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Item implements ChildItemAwareInterface
{
    const CAUSE_INVITE = 'invite';
    const CAUSE_SHARE = 'share';
    const STATUS_FINISHED = 'finished';
    const STATUS_OFFERED = 'offered';
    const STATUS_DEFAULT = self::STATUS_FINISHED;
    const EXPIRATION_INTERVAL = '+ 1 day';
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Directory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="childItems", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $parentList;

    /**
     * @var Directory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory")
     * @ORM\JoinColumn(nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    protected $previousList;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    protected $secret;

    /**
     * @var string
     *
     * @ORM\Column(type="string", options={"default": \App\DBAL\Types\Enum\NodeEnumType::TYPE_CRED})
     */
    protected $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdated;

    /**
     * @var Item|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="sharedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $originalItem;

    /**
     * @var Item[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="originalItem", cascade={"remove"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $sharedItems;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $favorite = false;

    /**
     * @var Tag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="item_tags",
     *     joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $tags;

    /**
     * @var string|null
     *
     * @ORM\Column(type="AccessEnumType", nullable=true)
     */
    protected $access;

    /**
     * @var ItemUpdate|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ItemUpdate", mappedBy="item", orphanRemoval=true, cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $update;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 0}, nullable=false)
     */
    protected $sort = 0;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=510, nullable=true)
     */
    protected $link;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true, options={"default"="invite"})
     */
    protected $cause;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, options={"default"="finished"}, length=10)
     */
    protected $status = self::STATUS_DEFAULT;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ownedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var Team|null
     */
    protected $team;

    /**
     * Item constructor.
     * @throws \Exception
     */
    public function __construct(?User $user = null)
    {
        $this->id = Uuid::uuid4();
        $this->originalItem = null;
        $this->type = NodeEnumType::TYPE_CRED;
        $this->sharedItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->owner = $user;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Directory|null
     */
    public function getParentList(): ?Directory
    {
        return $this->parentList;
    }

    /**
     * @param Directory $parentList
     */
    public function setParentList(Directory $parentList)
    {
        $this->parentList = $parentList;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param string|null $secret
     */
    public function setSecret(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated(): \DateTime
    {
        return $this->lastUpdated;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function refreshLastUpdated()
    {
        $this->lastUpdated = new \DateTime();
    }

    /**
     * @return Item|null
     */
    public function getOriginalItem(): ?Item
    {
        return $this->originalItem;
    }

    /**
     * @param Item|null $originalItem
     */
    public function setOriginalItem(Item $originalItem): void
    {
        $this->originalItem = $originalItem;
    }

    /**
     * @return Item[]|Collection
     */
    public function getSharedItems(): Collection
    {
        return $this->sharedItems;
    }

    /**
     * @param Collection $sharedItems
     */
    public function setSharedItems(Collection $sharedItems)
    {
        $this->sharedItems = $sharedItems;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     */
    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param iterable|Tag[] $tags
     */
    public function setTags(iterable $tags): void
    {
        $this->tags = $tags;
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getAccess(): ?string
    {
        return $this->access;
    }

    public function setAccess(?string $access): void
    {
        $this->access = $access;
    }

    public function getUpdate(): ?ItemUpdate
    {
        return $this->update;
    }

    public function setUpdate(?ItemUpdate $update): void
    {
        $this->update = $update;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
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
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Directory|null
     */
    public function getPreviousList(): ?Directory
    {
        return $this->previousList;
    }

    /**
     * @param Directory|null $previousList
     */
    public function setPreviousList(?Directory $previousList): void
    {
        $this->previousList = $previousList;
    }

    public function getOwner(): ?User
    {
        return $this->originalItem ? $this->originalItem->getOwner() : $this->owner;
    }

    public function getSignedOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
