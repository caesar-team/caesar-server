<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Team\AwareOwnerAndTeamInterface;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\UniqueTeamKeypair
 */
final class CreateKeypairRequest implements AwareOwnerAndTeamInterface
{
    private ?User $owner;

    private ?Team $team;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    private ?Item $relatedItem;

    private User $user;

    public function __construct(User $user)
    {
        $this->owner = $user;
        $this->user = $user;
        $this->team = null;
        $this->relatedItem = null;
        $this->secret = null;
    }

    public function getOwner(): ?User
    {
        return $this->owner ?? $this->getUser();
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getRelatedItem(): ?Item
    {
        return $this->relatedItem;
    }

    public function setRelatedItem(?Item $relatedItem): void
    {
        $this->relatedItem = $relatedItem;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
