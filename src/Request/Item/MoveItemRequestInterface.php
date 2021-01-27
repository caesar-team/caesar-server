<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;

interface MoveItemRequestInterface
{
    public function getItem(): Item;

    public function getSecret(): ?string;

    public function getRaws(): ?string;
}
