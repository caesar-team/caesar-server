<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\DirectoryItem;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Directory\UserDirectory;
use App\Entity\Embedded\ItemMeta;
use App\Utils\ChildItemAwareInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $secret;

    /**
     * @var ItemMeta
     *
     * @ORM\Embedded(class="App\Entity\Embedded\ItemMeta")
     */
    private $meta;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $raws;

    /**
     * @var string
     *
     * @ORM\Column(type="string", options={"default": \App\DBAL\Types\Enum\NodeEnumType::TYPE_CRED})
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastUpdated;

    /**
     * @var Item|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="sharedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originalItem;

    /**
     * @var Item[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="originalItem", cascade={"remove"}, orphanRemoval=true, fetch="EAGER")
     */
    private $sharedItems;

    /**
     * @var Tag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="item_tags",
     *     joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $tags;

    /**
     * @var string|null
     *
     * @ORM\Column(type="AccessEnumType", nullable=true)
     */
    private $access;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=510, nullable=true)
     */
    private $link;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true, options={"default": "invite"})
     */
    private $cause;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, options={"default": "finished"}, length=10)
     */
    private $status = self::STATUS_DEFAULT;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ownedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $owner;

    /**
     * @var Team|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="ownedItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $team;

    /**
     * @var Item|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="keyPairItems", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $relatedItem;

    /**
     * @var Item[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="relatedItem")
     */
    private $keyPairItems;

    /**
     * @var Collection<int, FavoriteTeamItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\FavoriteTeamItem", mappedBy="item", cascade={"remove"}, orphanRemoval=true, fetch="EAGER")
     */
    private Collection $teamFavorites;

    /**
     * @var Collection<int, FavoriteUserItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\FavoriteUserItem", mappedBy="item", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EAGER")
     */
    private Collection $userFavorites;

    /**
     * @var Collection<int, DirectoryItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory\DirectoryItem", mappedBy="item", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private Collection $directoryItems;

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
        $this->keyPairItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->meta = new ItemMeta();
        $this->owner = $user;
        $this->teamFavorites = new ArrayCollection();
        $this->userFavorites = new ArrayCollection();
        $this->directoryItems = new ArrayCollection();
    }

    public function getParentList()
    {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getMeta(): ItemMeta
    {
        return $this->meta;
    }

    public function setMeta(ItemMeta $meta): void
    {
        $this->meta = $meta;
    }

    public function setRaws(?string $raws): void
    {
        $this->raws = $raws;
    }

    public function getRaws(): ?string
    {
        return $this->raws;
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
        foreach ($this->getOwnerSharedItems($cause) as $childItem) {
            $userId = $childItem->getSignedOwner()->getId()->toString();
            $uniqueUsers[$userId] = $childItem;
        }

        return array_values($uniqueUsers);
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isSystemType(): bool
    {
        return NodeEnumType::TYPE_SYSTEM === $this->type;
    }

    public function isKeyPairType(): bool
    {
        return NodeEnumType::TYPE_KEYPAIR === $this->type;
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
        //Should not set owner as null
        if (null === $owner && null !== $this->owner) {
            return;
        }
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

    public function getRelatedItem(): ?Item
    {
        return $this->relatedItem;
    }

    public function setRelatedItem(?Item $relatedItem): void
    {
        $this->relatedItem = $relatedItem;
    }

    public function hasSystemItems(): bool
    {
        return 0 !== $this->keyPairItems->count();
    }

    /**
     * @return Item[]
     */
    public function getKeyPairItems(): array
    {
        return $this->keyPairItems->toArray();
    }

    public function setKeyPairItems(Collection $sharedItems): void
    {
        $this->keyPairItems = $sharedItems;
    }

    public function removeKeypairItem(Item $item): void
    {
        $this->keyPairItems->removeElement($item);
    }

    public function getKeyPairItemByUser(User $user): ?Item
    {
        /**
         * @psalm-suppress UndefinedInterfaceMethod
         */
        $systemItem = $this->keyPairItems->filter(function (Item $item) use ($user) {
            return $item->getSignedOwner()->equals($user);
        })->first();

        return $systemItem instanceof Item ? $systemItem : null;
    }

    public function getKeyPairItemsWithoutRoot(): array
    {
        return $this->keyPairItems->filter(function (Item $item) {
            return !$this->getOwner()->equals($item->getOwner());
        })->toArray();
    }

    public function getTeamKeypairGroupKey(): string
    {
        $groups = [];
        if (null !== $this->team) {
            $groups[] = $this->team->getId()->toString();
        }
        if (null !== $this->owner) {
            $groups[] = $this->owner->getId()->toString();
        }
        if (null !== $this->relatedItem) {
            $groups[] = $this->relatedItem->getId()->toString();
        }

        return implode('', $groups);
    }

    public function hasAnonymousUser(): bool
    {
        return $this->owner->hasRole(User::ROLE_ANONYMOUS_USER);
    }

    public function isNotDeletable(): bool
    {
        if (null !== $this->getTeam()) {
            $directory = $this->getTeamDirectory();
        } else {
            $directory = $this->getOwnerDirectory();
        }

        return NodeEnumType::TYPE_KEYPAIR !== $this->getType()
            && (null === $directory || DirectoryEnumType::TRASH !== $directory->getType())
        ;
    }

    /**
     * @return FavoriteTeamItem[]
     */
    public function getTeamFavorites(): array
    {
        return $this->teamFavorites->toArray();
    }

    public function addTeamFavorite(FavoriteTeamItem $favoriteTeamItem): void
    {
        if (!$this->teamFavorites->contains($favoriteTeamItem)) {
            $this->teamFavorites->add($favoriteTeamItem);
        }
    }

    /**
     * @return FavoriteUserItem[]
     */
    public function getUserFavorites(): array
    {
        return $this->userFavorites->toArray();
    }

    public function findUserFavorite(User $user): ?FavoriteUserItem
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('user', $user));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $favorite = $this->userFavorites->matching($criteria)->first();

        return $favorite instanceof FavoriteUserItem ? $favorite : null;
    }

    public function isFavoriteByUser(User $user): bool
    {
        $favorite = $this->findUserFavorite($user);

        return null !== $favorite;
    }

    public function addUserFavorite(FavoriteUserItem $favoriteUserItem): void
    {
        if (!$this->userFavorites->contains($favoriteUserItem)) {
            $this->userFavorites->add($favoriteUserItem);
            $favoriteUserItem->setItem($this);
        }
    }

    /**
     * @return DirectoryItem[]
     */
    public function getDirectoryItems(): array
    {
        return $this->directoryItems->toArray();
    }

    public function addDirectoryItem(DirectoryItem $directory): void
    {
        if (!$this->directoryItems->contains($directory)) {
            $this->directoryItems->add($directory);
            $directory->setItem($this);
        }
    }

    /**
     * @return DirectoryItem
     */
    public function getDirectoryItemByDirectory(AbstractDirectory $directory): ?DirectoryItem
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('directory', $directory));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $directoryItem = $this->directoryItems->matching($criteria)->first();

        return $directoryItem instanceof DirectoryItem ? $directoryItem : null;
    }

    public function getCurrentDirectoryItemByTeam(Team $team): ?DirectoryItem
    {
        $directoryItem = $this->directoryItems->filter(static function (DirectoryItem $directoryItem) use ($team) {
            $teamDirectory = $directoryItem->getDirectory();
            if (!$teamDirectory instanceof TeamDirectory) {
                return false;
            }

            return $teamDirectory->getTeam()->equals($team);
        })->first();

        if (!$directoryItem instanceof DirectoryItem) {
            return null;
        }

        return $directoryItem;
    }

    public function getCurrentDirectoryItemByUser(User $user): ?DirectoryItem
    {
        $directoryItem = $this->directoryItems->filter(static function (DirectoryItem $directoryItem) use ($user) {
            $userDirectory = $directoryItem->getDirectory();
            if (!$userDirectory instanceof UserDirectory) {
                return false;
            }

            return $userDirectory->getUser()->equals($user);
        })->first();

        if (!$directoryItem instanceof DirectoryItem) {
            return null;
        }

        return $directoryItem;
    }

    public function getCurrentDirectoryByUser(User $user): ?AbstractDirectory
    {
        $directoryItem = $this->getCurrentDirectoryItemByUser($user);
        if (!$directoryItem instanceof DirectoryItem) {
            return null;
        }

        return $directoryItem->getDirectory();
    }

    public function getOwnerDirectory(): ?AbstractDirectory
    {
        $directoryItem = $this->getCurrentDirectoryItemByUser($this->owner);
        if (!$directoryItem instanceof DirectoryItem) {
            return null;
        }

        return $directoryItem->getDirectory();
    }

    public function getTeamDirectory(): ?AbstractDirectory
    {
        if (null == $this->team) {
            return null;
        }

        $directoryItem = $this->getCurrentDirectoryItemByTeam($this->team);
        if (!$directoryItem instanceof DirectoryItem) {
            return null;
        }

        return $directoryItem->getDirectory();
    }
}
