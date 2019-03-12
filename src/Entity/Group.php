<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Group
 * @ORM\Entity
 * @ORM\Table(name="groups")
 */
class Group
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
     * @var UserGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="group", cascade={"persist"}, orphanRemoval=true)
     */
    private $userGroups;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $alias;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $title;

    /**
     * Group constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->userGroups = new ArrayCollection();
    }

    /**
     * @return UserGroup[]|Collection
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): void
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->setGroup($this);
        }
    }

    public function removeUserGroup(UserGroup $userGroup): void
    {
        $this->userGroups->removeElement($userGroup);
    }

    public function setUserGroups($userGroups): void
    {
        $this->userGroups = $userGroups;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
}