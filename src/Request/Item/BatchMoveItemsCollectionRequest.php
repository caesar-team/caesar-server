<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory;

final class BatchMoveItemsCollectionRequest
{
    /**
     * @var BatchMoveItemRequest[]
     */
    private array $moveItemRequests = [];

    private Directory $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return BatchMoveItemRequest[]
     */
    public function getMoveItemRequests(): array
    {
        return $this->moveItemRequests;
    }

    /**
     * @param BatchMoveItemRequest[] $moveItemRequests
     */
    public function setMoveItemRequests(array $moveItemRequests): void
    {
        $this->moveItemRequests = $moveItemRequests;
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }
}
