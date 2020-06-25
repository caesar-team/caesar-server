<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Utils\ChildItemAwareInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Item implements ChildItemAwareInterface
{
    public const CAUSE_INVITE = 'invite';
    public const CAUSE_SHARE = 'share';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_OFFERED = 'offered';
    public const STATUS_DEFAULT = self::STATUS_FINISHED;
    public const EXPIRATION_INTERVAL = '+ 1 day';
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
     * @ORM\Column(type="string", length=10, nullable=true, options={"default": "invite"})
     */
    protected $cause;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, options={"default": "finished"}, length=10)
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="ownedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $team;

    /**
     * Item constructor.
     *
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

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getParentList(): ?Directory
    {
        return $this->parentList;
    }

    public function setParentList(Directory $parentList)
    {
        $this->parentList = $parentList;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getLastUpdated(): DateTime
    {
        return $this->lastUpdated;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function refreshLastUpdated(): void
    {
        $this->lastUpdated = new DateTime();
    }

    public function getOriginalItem(): ?Item
    {
        return $this->originalItem;
    }

    public function getOriginalItemId(): ?string
    {
        return null !== $this->originalItem ? $this->originalItem->getId()->toString() : null;
    }

    /**
     * @return Item[]
     */
    public function getOwnerSharedItems(string $cause = Item::CAUSE_INVITE): array
    {
        $ownerItem = null !== $this->getOriginalItem() ? $this->getOriginalItem() : $this;

        return $ownerItem->getSharedItems()
            ->filter(function (ChildItemAwareInterface $childItem) use ($cause) {
                return $cause === $childItem->getCause();
            })
            ->toArray()
        ;
    }

    /**
     * @return Item[]
     */
    public function getUniqueOwnerShareItems(string $cause = Item::CAUSE_INVITE): array
    {
        $uniqueUsers = [];

        return array_values(array_filter($this->getOwnerSharedItems($cause), static function (ChildItemAwareInterface $childItem) use (&$uniqueUsers) {
            $userId = $childItem->getSignedOwner()->getId()->toString();
            if (!in_array($userId, $uniqueUsers)) {
                $uniqueUsers[] = $userId;

                return true;
            }

            return false;
        }));
    }

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

    public function setSharedItems(Collection $sharedItems): void
    {
        $this->sharedItems = $sharedItems;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    public function toggleFavorite(): void
    {
        $this->favorite = !$this->favorite;
    }

    public function getType(): string
    {
        return $this->type;
    }

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
     * @param Collection|Tag[] $tags
     */
    public function setTags(Collection $tags): void
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

    public function clearUpdate(): void
    {
        $this->update = null;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getCause(): ?string
    {
        return $this->cause;
    }

    public function setCause(?string $cause): void
    {
        $this->cause = $cause;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPreviousList(): ?Directory
    {
        return $this->previousList;
    }

    public function getPreviousListId(): ?string
    {
        return null !== $this->previousList ? $this->previousList->getId()->toString() : null;
    }

    public function setPreviousList(?Directory $previousList): void
    {
        $this->previousList = $previousList;
    }

    public function getOwner(): ?User
    {
        return $this->originalItem ? $this->originalItem->getOwner() : $this->owner;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
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

    public function getTeamId(): ?string
    {
        return null !== $this->team ? $this->team->getId()->toString() : null;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
