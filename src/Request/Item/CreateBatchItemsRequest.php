<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\User;

final class CreateBatchItemsRequest
{
    /**
     * @var CreateItemRequest[]
     */
    private array $items;

    private User $user;

    public function __construct(User $user)
    {
        $this->items = [];
        $this->user = $user;
    }

    /**
     * @return CreateItemRequest[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param CreateItemRequest[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}