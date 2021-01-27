<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\Team;

interface MoveTeamItemRequestInterface
{
    public function getItem(): Item;

    public function getTeam(): Team;

    public function getDirectory(): AbstractDirectory;

    public function getSecret(): ?string;
}
