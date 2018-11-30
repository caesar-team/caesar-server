<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Post
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var Directory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="childPosts", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $parentList;

    /**
     * @var array
     *
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    protected $secret;

    /**
     * @var string
     *
     * @ORM\Column(type="string", options={"default"=\App\DBAL\Types\Enum\NodeEnumType::TYPE_CRED})
     */
    protected $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $lastUpdated;

    /**
     * @var Post|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="sharedPosts", cascade={"persist"})
     */
    protected $originalPost;

    /**
     * @var Post[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="originalPost", orphanRemoval=true)
     */
    protected $sharedPosts;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="false"})
     */
    protected $favorite = false;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->originalPost = null;
        $this->type = NodeEnumType::TYPE_CRED;
        $this->sharedPosts = new ArrayCollection();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Directory|null
     */
    public function getParentList(): ?Directory
    {
        return $this->parentList;
    }

    /**
     * @param Directory $parentList
     */
    public function setParentList(Directory $parentList)
    {
        $this->parentList = $parentList;
    }

    /**
     * @return array|null
     */
    public function getSecret(): ?array
    {
        return $this->secret;
    }

    /**
     * @param array|null $secret
     */
    public function setSecret(array $secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated(): \DateTime
    {
        return $this->lastUpdated;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function refreshLastUpdated()
    {
        $this->lastUpdated = new \DateTime();
    }

    /**
     * @return Post|null
     */
    public function getOriginalPost(): ?Post
    {
        return $this->originalPost;
    }

    /**
     * @param Post|null $originalPost
     */
    public function setOriginalPost(Post $originalPost): void
    {
        $this->originalPost = $originalPost;
    }

    /**
     * @return Post[]|Collection
     */
    public function getSharedPosts(): Collection
    {
        return $this->sharedPosts;
    }

    /**
     * @param Collection $sharedPosts
     */
    public function setSharedPosts(Collection $sharedPosts)
    {
        $this->sharedPosts = $sharedPosts;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     */
    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
