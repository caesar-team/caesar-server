<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class EditItemRequest
{
    private ?User $owner;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    private array $tags;

    private Item $item;

    public function __construct(Item $item)
    {
        $this->tags = $item->getTags()->toArray();
        $this->secret = $item->getSecret();
        $this->owner = $item->getSignedOwner();
        $this->item = $item;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
