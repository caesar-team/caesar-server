<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SharePostRequest
{
    /**
     * @var User[]|Collection
     */
    protected $users;

    /**
     * @var Post
     */
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->users = new ArrayCollection();
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        if (false === $this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user)
    {
        $this->removeUser($user);
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }
}
