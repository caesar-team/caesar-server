<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
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
class Item
{
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
     * @var Directory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="childItems", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $parentList;

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
     */
    protected $originalItem;

    /**
     * @var Item[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="originalItem", orphanRemoval=true)
     */
    protected $sharedItems;

    /**
     * @var ShareItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ShareItem", mappedBy="item", orphanRemoval=true)
     */
    protected $externalSharedItems;

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
     * @ORM\OneToOne(targetEntity="App\Entity\ItemUpdate", mappedBy="item", orphanRemoval=true, cascade={"persist"})
     */
    protected $update;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 0}, nullable=false)
     */
    protected $sort = 0;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $cause;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->originalItem = null;
        $this->type = NodeEnumType::TYPE_CRED;
        $this->sharedItems = new ArrayCollection();
        $this->externalSharedItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
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
     * @return ShareItem[]|Collection
     */
    public function getExternalSharedItems(): Collection
    {
        return $this->externalSharedItems;
    }

    /**
     * @param ShareItem[]|Collection $externalSharedItems
     */
    public function setExternalSharedItems(Collection $externalSharedItems): void
    {
        $this->externalSharedItems = $externalSharedItems;
    }

    public function addExternalShareItem(ShareItem $shareItem): void
    {
        if (!$this->externalSharedItems->contains($shareItem)) {
            $this->externalSharedItems->add($shareItem);
            $shareItem->setItem($this);
        }
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
}
