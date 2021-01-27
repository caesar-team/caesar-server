<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\User;

interface MovePersonalItemRequestInterface
{
    public function getItem(): Item;

    public function getUser(): User;

    public function getDirectory(): AbstractDirectory;

    public function getSecret(): ?string;
}
