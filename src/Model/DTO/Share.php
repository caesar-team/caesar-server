<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;
use App\Entity\User;

final class Share
{
    private User $user;

    private Item $keypair;

    public function __construct(User $user, Item $keypair)
    {
        $this->user = $user;
        $this->keypair = $keypair;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getKeypair(): Item
    {
        return $this->keypair;
    }
}
