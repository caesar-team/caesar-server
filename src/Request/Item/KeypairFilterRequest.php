<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\User;

final class KeypairFilterRequest
{
    public const TYPE_TEAM = 'team';
    public const TYPE_PERSONAL = 'personal';

    private ?string $type;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->type = null;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function hasPersonalType(): bool
    {
        return self::TYPE_PERSONAL === $this->type;
    }

    public function hasTeamType(): bool
    {
        return self::TYPE_TEAM === $this->type;
    }
}
