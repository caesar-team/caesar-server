<?php

declare(strict_types=1);

namespace App\Entity;

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
        $this->userTeams = new ArrayCollection();
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