<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\DirectoryEnumType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LogicException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\DirectoryRepository")
 */
class Directory
{
    public const LIST_DEFAULT = 'default';
    public const LIST_TRASH = 'trash';
    public const LIST_ROOT_LIST = 'lists';
    public const LIST_INBOX = 'inbox';

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var Collection|Directory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory", mappedBy="parentList", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sort": "ASC", "createdAt": "DESC"})
     */
    private $childLists;

    /**
     * @var Directory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="childLists")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    private $parentList;

    /**
     * @var Collection|Item[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="parentList", cascade={"remove"})
     * @ORM\OrderBy({"lastUpdated": "DESC"})
     */
    private $childItems;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $label;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": 0}, nullable=false)
     * @Gedmo\SortablePosition
     */
    private $sort = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="DirectoryEnumType")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="directories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Team $team;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="directories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?User $user;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $createdAt;

    /**
     * @var User|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="inbox")
     */
    private $userInbox;

    /**
     * @var User|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="lists")
     */
    private $userLists;

    /**
     * @var User|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="trash")
     */
    private $userTrash;

    public function __construct(string $label = null)
    {
        $this->id = Uuid::uuid4();
        $this->team = null;
        $this->user = null;
        $this->type = DirectoryEnumType::LIST;
        $this->childLists = new ArrayCollection();
        $this->childItems = new ArrayCollection();
        if (null !== $label) {
            $this->label = $label;
        }
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function createTrash(): self
    {
        $list = new self(self::LIST_TRASH);
        $list->type = DirectoryEnumType::TRASH;

        return $list;
    }

    public static function createRootList(): self
    {
        $list = new self(self::LIST_ROOT_LIST);
        $list->type = DirectoryEnumType::ROOT;

        return $list;
    }

    public static function createDefaultList(): self
    {
        $list = new self(self::LIST_DEFAULT);
        $list->type = DirectoryEnumType::DEFAULT;

        return $list;
    }

    public static function createInbox(): self
    {
        $list = new self(self::LIST_INBOX);
        $list->type = DirectoryEnumType::INBOX;

        return $list;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return array<Item>
     */
    public function getChildItems(string $status = null): array
    {
        if ($status) {
            return array_filter($this->childItems->toArray(), function (Item $item) use ($status) {
                return $status === $item->getStatus();
            });
        }

        return $this->childItems->toArray();
    }

    public function addChildItem(Item $item): void
    {
        if (false === $this->childItems->contains($item)) {
            $this->childItems->add($item);
            $item->setParentList($this);
        }
    }

    public function removeChildItem(Item $item): void
    {
        $this->childItems->removeElement($item);
    }

    /**
     * @return Directory[]|Collection
     */
    public function getChildLists(): Collection
    {
        return $this->childLists;
    }

    public function hasChildListByDirectory(Directory $directory): bool
    {
        return $this->childLists->contains($directory);
    }

    public function addChildList(Directory $directory): void
    {
        if (false === $this->childLists->contains($directory)) {
            $this->childLists->add($directory);
            $directory->setParentList($this);
            $directory->setUser($this->getUser());
        }
    }

    public function getParentList(): ?Directory
    {
        return $this->parentList;
    }

    public function setParentList(?Directory $parentList): void
    {
        if ($parentList === $this) {
            throw new LogicException('Can not be self parent');
        }
        $this->parentList = $parentList;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        // @todo remove after implemented inbox
        if (null !== $this->getUserInbox()) {
            return 'Shared with me';
        }

        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function equals(?Directory $directory): bool
    {
        if (null === $directory) {
            return false;
        }

        return $this->getId()->toString() === $directory->getId()->toString();
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
        $team->addDirectory($this);
    }

    public function isTrashDirectory(): bool
    {
        return DirectoryEnumType::TRASH === $this->getType();
    }

    public function isDefaultDirectory(): bool
    {
        return DirectoryEnumType::DEFAULT === $this->getType();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUserInbox(): ?User
    {
        return $this->userInbox;
    }

    public function setUserInbox(?User $userInbox): void
    {
        $this->userInbox = $userInbox;
    }

    public function getUserLists(): ?User
    {
        return $this->userLists;
    }

    public function setUserLists(?User $userLists): void
    {
        $this->userLists = $userLists;
    }

    public function getUserTrash(): ?User
    {
        return $this->userTrash;
    }

    public function setUserTrash(?User $userTrash): void
    {
        $this->userTrash = $userTrash;
    }

    public function __toString(): string
    {
        $type = 'personal';
        if ($this->getTeam()) {
            $type = 'team';
        }

        return sprintf('%s (%s)', $this->getLabel(), $type);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
