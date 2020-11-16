<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;
use Symfony\Component\Validator\Constraints as Assert;

final class ShareBatchItemRequest
{
    /**
     * @var ShareItemRequest[]
     *
     * @Assert\Valid
     */
    private $users = [];

    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @return ShareItemRequest[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param ShareItemRequest[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
