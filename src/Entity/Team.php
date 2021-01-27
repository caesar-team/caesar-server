<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\TeamDirectory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Group.
 *
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ORM\Table(name="groups",
 *     uniqueConstraints={
 *         @UniqueConstraint(name="unique_alias", columns={"alias"}),
 *         @UniqueConstraint(name="unique_title", columns={"title"}),
 *     }
 * )
 * @UniqueEntity(fields={"title"}, message="You already have a team with the same name. Choose another name.")
 */
class Team
{
    public const DEFAULT_GROUP_ALIAS = 'default';
    public const DEFAULT_GROUP_TITLE = 'Default';
    public const OTHER_TYPE = 'other';

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
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $icon;

    /**
     * @var Collection|Item[]
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="team")
     */
    private $ownedItems;

    /**
     * @var Collection<int, TeamDirectory>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory\TeamDirectory", mappedBy="team", cascade={"persist"})
     * @ORM\OrderBy({"sort": "ASC"})
     */
    private Collection $directories;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $pinned = [];

    /**
     * @var Collection<int, FavoriteTeamItem>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\FavoriteTeamItem", mappedBy="team", cascade={"remove"}, orphanRemoval=true, fetch="EAGER")
     */
    private Collection $itemFavorites;

    /**
     * Group constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->title = '';
        $this->userTeams = new ArrayCollection();
        $this->ownedItems = new ArrayCollection();
        $this->directories = new ArrayCollection();
        $this->itemFavorites = new ArrayCollection();
    }

    /**
     * @return UserTeam[]|Collection
     */
    public function getUserTeams(): Collection
    {
        return $this->userTeams;
    }

    public function getUserTeamsWithoutPretender(): array
    {
        return $this->getUserTeams()->filter(static function (UserTeam $userTeam) {
            return UserTeam::USER_ROLE_PRETENDER !== $userTeam->getUserRole();
        })->toArray();
    }

    /**
     * @return UserTeam[]
     */
    public function getAdminUserTeams(array $excludes = []): array
    {
        return $this->getUserTeams()->filter(static function (UserTeam $userTeam) use ($excludes) {
            return UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole()
                && !in_array($userTeam->getUser()->getId()->toString(), $excludes)
            ;
        })->toArray();
    }

    /**
     * @return UserTeam[]
     */
    public function getMemberUserTeams(): array
    {
        return $this->getUserTeams()->filter(static function (UserTeam $userTeam) {
            return UserTeam::USER_ROLE_MEMBER === $userTeam->getUserRole();
        })->toArray();
    }

    public function getUserTeamByUser(?UserInterface $user): ?UserTeam
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('user', $user));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $userTeam = $this->userTeams->matching($criteria)->first();

        return $userTeam instanceof UserTeam ? $userTeam : null;
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

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

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

    public function getDefaultDirectory(): TeamDirectory
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('type', DirectoryEnumType::DEFAULT));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        return $this->directories->matching($criteria)->first();
    }

    public function getLists(): TeamDirectory
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('type', DirectoryEnumType::ROOT));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        return $this->directories->matching($criteria)->first();
    }

    public function getTrash(): TeamDirectory
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('type', DirectoryEnumType::TRASH));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        return $this->directories->matching($criteria)->first();
    }

    /**
     * @return Item[]
     */
    public function getOwnedItems(): array
    {
        return $this->ownedItems->toArray();
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

    /**
     * @return TeamDirectory[]
     */
    public function getDirectories(): array
    {
        return $this->directories->toArray();
    }

    /**
     * @return TeamDirectory[]
     */
    public function getDirectoriesWithoutRoot(): array
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('type', DirectoryEnumType::ROOT));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        return $this->directories->matching($criteria)->toArray();
    }

    public function getDirectoryByLabel(?string $label): ?TeamDirectory
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('label', $label));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $directory = $this->directories->matching($criteria)->first();

        return $directory instanceof TeamDirectory ? $directory : null;
    }

    public function addDirectory(TeamDirectory $directory): void
    {
        if (!$this->directories->contains($directory)) {
            $this->directories->add($directory);
            $directory->setTeam($this);
        }
    }

    public function getPinned(): array
    {
        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         * @psalm-suppress DocblockTypeContradiction
         */
        return null !== $this->pinned ? $this->pinned : [];
    }

    public function setPinned(array $pinned): void
    {
        $this->pinned = $pinned;
    }

    public function togglePinned(User $user, bool $pin = true): void
    {
        $pinned = $this->getPinned();
        if ($this->hasPinned($user) && !$pin) {
            unset($pinned[$user->getId()->toString()]);
        } elseif (!$this->hasPinned($user) && $pin) {
            $pinned[$user->getId()->toString()] = $user->getId()->toString();
        }

        $this->setPinned($pinned);
    }

    public function hasPinned(User $user): bool
    {
        return in_array($user->getId()->toString(), $this->getPinned());
    }

    public function isPinned(User $user): bool
    {
        return !in_array($user->getId()->toString(), $this->getPinned());
    }

    /**
     * @return FavoriteTeamItem[]
     */
    public function getItemFavorites(): array
    {
        return $this->itemFavorites->toArray();
    }

    public function addItemFavorite(FavoriteTeamItem $favoriteUserItem): void
    {
        if (!$this->itemFavorites->contains($favoriteUserItem)) {
            $this->itemFavorites->add($favoriteUserItem);
            $favoriteUserItem->setTeam($this);
        }
    }

    public function equals(?Team $team): bool
    {
        return null !== $team && $this->getId()->toString() === $team->getId()->toString();
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
