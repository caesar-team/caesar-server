<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Item;
use App\Entity\User;

interface AwareOwnerAndRelatedItemInterface
{
    public function getOwner(): ?User;

    public function getRelatedItem(): ?Item;
}
