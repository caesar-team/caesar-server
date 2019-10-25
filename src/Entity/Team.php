<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DefaultIcon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Class Group
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ORM\Table(name="groups",
 *    uniqueConstraints={
 *        @UniqueConstraint(name="unique_alias",
 *            columns={"alias"}),
 *        @UniqueConstraint(name="unique_title",
 *            columns={"title"}),
 *    }
 * )
 */
class Team
{
    const DEFAULT_GROUP_ALIAS = 'default';
    const DEFAULT_GROUP_TITLE = 'Default';
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var UserTeam[]|Collection
     *
     * @ORM\OneToMany(targetEntity="UserTeam", mappedBy="team", cascade={"persist"}, orphanRemoval=true)
     */
    private $userTeams;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $alias;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $icon;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist"}
     * )
     */
    protected $lists;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist"}
     * )
     */
    protected $trash;

    /**
     * @var Collection|Item[]
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="owner")
     */
    private $ownedItems;

    /**
     * Group constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->userTeams = new ArrayCollection();
        $this->lists = Directory::createRootList();
        $this->lists->addChildList(Directory::createDefaultList());
        $this->trash = Directory::createTrash();
        $this->icon = DefaultIcon::getDefaultIcon();
        $this->ownedItems = new ArrayCollection();
    }

    /**
     * @return UserTeam[]|Collection
     */
    public function getUserTeams(): Collection
    {
        return $this->userTeams;
    }

    public function addUserTeam(UserTeam $userTeam): void
    {
        if (!$this->userTeams->contains($userTeam)) {
            $this->userTeams->add($userTeam);
            $userTeam->setTeam($this);
        }
    }

    public function removeUserTeam(UserTeam $userTeam): void
    {
        $this->userTeams->removeElement($userTeam);
    }

    public function setUserTeams($userTeams): void
    {
        $this->userTeams = $userTeams;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     */
    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getDefaultDirectory(): Directory
    {
        return array_reduce($this->getLists()->getChildLists()->toArray(),
            function (?Directory $prevDir, Directory $currDir) {
                if (is_null($prevDir)) {
                    return $currDir;
                }

                return Directory::LIST_DEFAULT === $prevDir->getLabel() ? $prevDir : $currDir;
            }
        );
    }

    /**
     * @return Directory
     */
    public function getLists(): Directory
    {
        return $this->lists;
    }

    /**
     * @return Directory
     */
    public function getTrash(): Directory
    {
        return $this->trash;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getOwnedItems(): Collection
    {
        return $this->ownedItems;
    }

    public function addOwnedItem(Item $item): void
    {
        if (!$this->ownedItems->contains($item)) {
            $this->ownedItems->add($item);
            $item->setTeam($this);
        }
    }

    public function removeOwnedItem(Item $item): void
    {
        $this->ownedItems->removeElement($item);
    }
}