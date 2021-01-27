<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use App\DBAL\Types\Enum\DirectoryEnumType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="directory")
 * @ORM\Entity(repositoryClass="App\Repository\DirectoryRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object", type="string")
 * @ORM\DiscriminatorMap({
 *     UserDirectory::class: UserDirectory::class,
 *     TeamDirectory::class: TeamDirectory::class
 * })
 */
abstract class AbstractDirectory
{
    public const LABEL_DEFAULT = 'default';
    public const LABEL_TRASH = 'trash';
    public const LABEL_ROOT_LIST = 'lists';
    public const LABEL_INBOX = 'inbox';

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Collection<int, AbstractDirectory>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory\AbstractDirectory", mappedBy="parentDirectory", cascade={"remove", "persist"})
     * @ORM\OrderBy({"sort": "ASC", "createdAt": "DESC"})
     */
    protected Collection $childDirectories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory\AbstractDirectory", inversedBy="childDirectories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    protected ?AbstractDirectory $parentDirectory;

    /**
     * @ORM\Column
     */
    protected string $label;

    /**
     * @ORM\Column(type="integer", options={"default": 0}, nullable=false)
     * @Gedmo\SortablePosition
     */
    protected int $sort;

    /**
     * @ORM\Column(type="DirectoryEnumType")
     */
    protected string $type;

    /**
     * @var Collection<int, DirectoryItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory\DirectoryItem", mappedBy="directory", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private Collection $directoryItems;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected \DateTimeImmutable $createdAt;

    public function __construct(string $label)
    {
        $this->id = Uuid::uuid4();
        $this->childDirectories = new ArrayCollection();
        $this->parentDirectory = null;
        $this->label = $label;
        $this->sort = 0;
        $this->type = DirectoryEnumType::LIST;
        $this->directoryItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    /**
     * @return AbstractDirectory[]
     */
    public function getChildDirectories(): array
    {
        return $this->childDirectories->toArray();
    }

    public function setChildDirectories(Collection $childDirectories): void
    {
        $this->childDirectories = $childDirectories;
    }

    public function addChildDirectory(AbstractDirectory $child): void
    {
        if (!$this->childDirectories->contains($child)) {
            $this->childDirectories->add($child);
        }
    }

    public function getParentDirectory(): ?AbstractDirectory
    {
        return $this->parentDirectory;
    }

    public function setParentDirectory(?AbstractDirectory $parentDirectory): void
    {
        $this->parentDirectory = $parentDirectory;
        if (null !== $parentDirectory) {
            $parentDirectory->addChildDirectory($this);
        }
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
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
            $directory->setDirectory($this);
        }
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function equals(?AbstractDirectory $directory): bool
    {
        if (null === $directory) {
            return false;
        }

        return $this->getId()->toString() === $directory->getId()->toString();
    }

    public function isRoot(): bool
    {
        return DirectoryEnumType::ROOT === $this->getType();
    }

    public function isDefault(): bool
    {
        return DirectoryEnumType::DEFAULT === $this->getType();
    }

    public function isInbox(): bool
    {
        return DirectoryEnumType::INBOX === $this->getType();
    }

    public function isTrash(): bool
    {
        return DirectoryEnumType::TRASH === $this->getType();
    }
}
