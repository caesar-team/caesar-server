<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="user_group",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_team_uqid",
 *         columns={"user_id", "group_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserTeamRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UserTeam
{
    use TimestampableEntity;

    public const DEFAULT_USER_ROLE = self::USER_ROLE_MEMBER;
    public const USER_ROLE_MEMBER = 'member';
    public const USER_ROLE_ADMIN = 'admin';
    public const USER_ROLE_GUEST = 'guest';
    public const USER_ROLE_PRETENDER = 'pretender';
    public const ROLES = [
        self::USER_ROLE_MEMBER,
        self::USER_ROLE_ADMIN,
        self::USER_ROLE_GUEST,
        self::USER_ROLE_PRETENDER,
    ];
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var Team|null
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="userTeams", cascade={"persist"})
     * @ORM\JoinColumn(name="group_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $team;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userTeams")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=50, options={"default": "member"})
     */
    private $userRole = self::DEFAULT_USER_ROLE;

    /**
     * UserGroup constructor.
     *
     * @param User $user
     * @param Team $team
     *
     * @throws \Exception
     */
    public function __construct(?User $user = null, ?Team $team = null, string $userRole = self::USER_ROLE_MEMBER)
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->team = $team;
        $this->userRole = $userRole;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(string $userRole): void
    {
        $this->userRole = $userRole;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function hasRole(string $role): bool
    {
        return $role === $this->userRole;
    }

    public function __toString(): string
    {
        return $this->getUser()->getUsername();
    }
}
