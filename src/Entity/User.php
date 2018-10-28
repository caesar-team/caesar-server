<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * User.
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends FOSUser
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $googleId;

    /**
     * @var Avatar|null
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Avatar",
     *     mappedBy="user",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $avatar;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $inbox;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $lists;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $trash;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $masterCreated = false;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = Uuid::uuid4();
        $this->inbox = Directory::createInbox();
        $this->lists = Directory::createRootList();
        $this->trash = Directory::createTrash();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @param string $googleId
     */
    public function setGoogleId(string $googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return Avatar|null
     */
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    /**
     * @param Avatar|null $avatar
     */
    public function setAvatar(?Avatar $avatar): void
    {
        $this->avatar = $avatar;
        $avatar->setUser($this);
    }

    /**
     * @return Directory
     */
    public function getInbox(): Directory
    {
        return $this->inbox;
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

    /**
     * @return bool
     */
    public function isMasterCreated(): bool
    {
        return $this->masterCreated;
    }

    /**
     * @param bool $masterCreated
     */
    public function setMasterCreated(bool $masterCreated): void
    {
        $this->masterCreated = $masterCreated;
    }
}
