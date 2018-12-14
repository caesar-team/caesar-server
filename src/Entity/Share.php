<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="shares")
 */
class Share
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shares", cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $owner;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="availableShares", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", columnDefinition="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var SharePost[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\SharePost", mappedBy="share", orphanRemoval=true)
     */
    private $sharedPosts;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->sharedPosts = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return SharePost[]|Collection
     */
    public function getSharedPosts(): Collection
    {
        return $this->sharedPosts;
    }

    public function addSharePost(SharePost $sharePost): void
    {
        if (!$this->sharedPosts->contains($sharePost)) {
            $this->sharedPosts->add($sharePost);
            $sharePost->setShare($this);
        }
    }

    public function removeSharePost(SharePost $sharePost): void
    {
        $this->sharedPosts->removeElement($sharePost);
    }

    /**
     * @param SharePost[]|Collection $sharedPosts
     */
    public function setSharedPosts(Collection $sharedPosts): void
    {
        $this->sharedPosts = $sharedPosts;
    }
}
