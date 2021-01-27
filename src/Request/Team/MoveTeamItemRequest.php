<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\Team;
use Symfony\Component\Validator\Constraints as Assert;

final class MoveTeamItemRequest implements MoveTeamItemRequestInterface
{
    /**
     * @Assert\NotBlank
     */
    private AbstractDirectory $directory;

    private ?string $secret;

    private ?string $raws;

    private Team $team;

    private Item $item;

    public function __construct(Team $team)
    {
        $this->secret = null;
        $this->raws = null;
        $this->team = $team;
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

    public function getRaws(): ?string
    {
        return $this->raws;
    }

    public function setRaws(?string $raws): void
    {
        $this->raws = $raws;
    }

    public function getTeam(): Team
    {
        return $this->team;
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
