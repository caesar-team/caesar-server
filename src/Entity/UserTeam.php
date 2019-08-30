<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class UserGroup
 * @ORM\Table(name="user_group")
 * @ORM\Entity(repositoryClass="App\Repository\UserTeamRepository")
 * @ORM\HasLifecycleCallbacks
 */
class UserTeam
{
    use TimestampableEntity;

    const DEFAULT_USER_ROLE = self::USER_ROLE_MEMBER;
    const USER_ROLE_MEMBER = 'member';
    const USER_ROLE_ADMIN = 'admin';
    const USER_ROLE_GUEST = 'guest';
    const USER_ROLE_PRETENDER = 'pretender';
    const ROLES =  [
        self::USER_ROLE_MEMBER,
        self::USER_ROLE_ADMIN,
        self::USER_ROLE_GUEST,
    ];
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="userTeams", cascade={"persist"})
     * @ORM\JoinColumn(name="group_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $team;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userTeams")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=50, options={"default"="member"})
     */
    private $userRole = self::DEFAULT_USER_ROLE;

    /**
     * UserGroup constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUserRole(): string
    {
        return $this->userRole;
    }

    /**
     * @param string $userRole
     */
    public function setUserRole(string $userRole): void
    {
        $this->userRole = $userRole;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }
}