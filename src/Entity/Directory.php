<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table
 * @ORM\Entity
 * @UniqueEntity(fields={"label"}, errorPath="label", message="list.create.label.already_exists")
 */
class Directory
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Collection|Directory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory", mappedBy="parentList", cascade={"remove"})
     */
    protected $childLists;

    /**
     * @var Directory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="childLists")
     */
    protected $parentList;

    /**
     * @var Collection|Item[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="parentList", cascade={"remove"})
     */
    protected $childItems;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(type="NodeEnumType")
     */
    protected $type = NodeEnumType::TYPE_LIST;

    public function __construct(string $label = null)
    {
        $this->id = Uuid::uuid4();
        $this->childLists = new ArrayCollection();
        $this->childItems = new ArrayCollection();
        if (null !== $label) {
            $this->label = $label;
        }
    }

    public static function createTrash()
    {
        $trashList = new self('TRASH');
        $trashList->type = NodeEnumType::TYPE_TRASH;

        return $trashList;
    }

    public static function createRootList()
    {
        $trashList = new self('LISTS');
        $trashList->type = NodeEnumType::TYPE_LIST;

        return $trashList;
    }

    public static function createInbox()
    {
        $trashList = new self('INBOX');
        $trashList->type = NodeEnumType::TYPE_LIST;

        return $trashList;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Item[]|Collection
     */
    public function getChildItems()
    {
        return $this->childItems;
    }

    public function addChildItem(Item $item)
    {
        if (false === $this->childItems->contains($item)) {
            $this->childItems->add($item);
            $item->setParentList($this);
        }
    }

    public function removeChildItem(Item $item)
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

    public function addChildList(Directory $directory)
    {
        if (false === $this->childLists->contains($directory)) {
            $this->childLists->add($directory);
            $directory->setParentList($this);
        }
    }

    /**
     * @return Directory|null
     */
    public function getParentList(): ?Directory
    {
        return $this->parentList;
    }

    /**
     * @param Directory|null $parentList
     */
    public function setParentList(?Directory $parentList)
    {
        if ($parentList === $this) {
            throw new \LogicException('Can not be self parent');
        }
        $this->parentList = $parentList;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel(?string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
