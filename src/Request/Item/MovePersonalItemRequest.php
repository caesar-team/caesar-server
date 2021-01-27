<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class MovePersonalItemRequest implements MovePersonalItemRequestInterface
{
    /**
     * @Assert\NotBlank
     */
    private AbstractDirectory $directory;

    private ?string $secret;

    private User $user;

    private Item $item;

    public function __construct(User $user)
    {
        $this->secret = null;
        $this->user = $user;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }

    public function setDirectory(AbstractDirectory $directory): void
    {
        $this->directory = $directory;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }
}
