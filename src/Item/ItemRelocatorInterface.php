<?php

declare(strict_types=1);

namespace App\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Request\Item\MovePersonalItemRequestInterface;
use App\Request\Team\MoveTeamItemRequestInterface;

interface ItemRelocatorInterface
{
    public function moveChildItems(AbstractDirectory $fromDirectory, AbstractDirectory $toDirectory): void;

    public function movePersonalItem(MovePersonalItemRequestInterface $request): void;

    public function moveTeamItem(MoveTeamItemRequestInterface $request): void;
}
