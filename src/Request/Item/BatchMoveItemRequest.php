<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;

final class BatchMoveItemRequest implements MoveItemRequestInterface
{
    private Item $item;

    private ?string $secret = null;

    private ?string $raws = null;

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getRaws(): ?string
    {
        return $this->raws;
    }

    public function setRaws(?string $raws): void
    {
        $this->raws = $raws;
    }
}
