<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;
use App\Entity\Team;

final class Vault
{
    private Team $team;

    private Item $keypair;

    public function __construct(Team $team, Item $keypair)
    {
        $this->team = $team;
        $this->keypair = $keypair;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getKeypair(): Item
    {
        return $this->keypair;
    }
}
