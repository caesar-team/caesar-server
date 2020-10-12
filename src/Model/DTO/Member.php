<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;
use App\Entity\UserTeam;

final class Member
{
    private UserTeam $userTeam;

    private Item $keypair;

    public function __construct(UserTeam $userTeam, Item $keypair)
    {
        $this->userTeam = $userTeam;
        $this->keypair = $keypair;
    }

    public function getUserTeam(): UserTeam
    {
        return $this->userTeam;
    }

    public function getKeypair(): Item
    {
        return $this->keypair;
    }
}
