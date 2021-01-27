<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_favorite_team_item", columns={"item_id", "team_id"})})
 */
class FavoriteTeamItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="teamFavorites", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Item $item;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="itemFavorites", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Team $team;

    public function __construct(Item $item, Team $team)
    {
        $this->id = Uuid::uuid4();
        $this->item = $item;
        $this->team = $team;
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

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }
}
