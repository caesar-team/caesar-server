<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Team;
use App\Validator\Constraints\UniqueEntityProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class EditTeamRequest
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @UniqueEntityProperty(
     *     entityClass="App\Entity\Team",
     *     field="title",
     *     currentEntityExpression="this.getTeam()",
     *     message="team.label.unique"
     * )
     */
    private $title;

    /**
     * @var string|null
     */
    private $icon;

    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $this->title = $team->getTitle();
        $this->icon = $team->getIcon();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
