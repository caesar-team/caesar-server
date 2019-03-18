<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class UserGroup
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserGroup
{
    use TimestampableEntity;

    const DEEFAULT_USER_ROLE = self::USER_ROLE_MEMBER;
    const USER_ROLE_MEMBER = 'member';
    const USER_ROLE_ADMIN = 'admin';
    const USER_ROLE_GUEST = 'guest';
    const USER_ROLE_PRETENDER = 'pretender';
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="userGroups", cascade={"persist"})
     * @ORM\JoinColumn(name="group_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $group;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userGroups")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=50, options={"default"="member"})
     */
    private $userRole = self::DEEFAULT_USER_ROLE;

    /**
     * UserGroup constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group): void
    {
        $this->group = $group;
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