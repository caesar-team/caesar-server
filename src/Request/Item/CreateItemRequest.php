<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateItemRequest
{
    private ?User $owner;

    private ?Directory $list;

    /**
     * @Assert\NotBlank()
     */
    private ?string $type;

    /**
     * @Assert\NotBlank()
     */
    private ?string $title;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    private bool $favorite;

    private array $tags;

    private User $user;

    public function __construct(User $user)
    {
        $this->owner = $user;
        $this->user = $user;
        $this->list = null;
        $this->type = null;
        $this->secret = null;
        $this->favorite = false;
        $this->tags = [];
    }

    public function getOwner(): ?User
    {
        return $this->owner ?? $this->getUser();
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getList(): ?Directory
    {
        return $this->list;
    }

    public function setList(?Directory $list): void
    {
        $this->list = $list;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTeam(): ?Team
    {
        return null !== $this->getList() ? $this->getList()->getTeam() : null;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
