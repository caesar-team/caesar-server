<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Favorite\FavoriteUserItemRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_favorite_user_item", columns={"item_id", "user_id"})})
 */
class FavoriteUserItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="userFavorites", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Item $item;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="itemFavorites", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private User $user;

    public function __construct(User $user, Item $item)
    {
        $this->id = Uuid::uuid4();
        $this->item = $item;
        $this->user = $user;

        $user->addItemFavorite($this);
        $item->addUserFavorite($this);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
