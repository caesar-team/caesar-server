<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\User;

final class BatchMovePersonalItemsRequest
{
    /**
     * @var MovePersonalItemRequest[]
     */
    private array $moveItemRequests = [];

    private AbstractDirectory $directory;

    private User $user;

    public function __construct(AbstractDirectory $directory, User $user)
    {
        $this->directory = $directory;
        $this->user = $user;
    }

    /**
     * @return MovePersonalItemRequest[]
     */
    public function getMoveItemRequests(): array
    {
        return $this->moveItemRequests;
    }

    /**
     * @param MovePersonalItemRequest[] $moveItemRequests
     */
    public function setMoveItemRequests(array $moveItemRequests): void
    {
        $this->moveItemRequests = $moveItemRequests;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
