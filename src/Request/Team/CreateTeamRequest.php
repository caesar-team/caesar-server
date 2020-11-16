<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Validator\Constraints\UniqueEntityProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTeamRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank
     * @UniqueEntityProperty(entityClass="App\Entity\Team", field="title", message="team.label.unique")
     */
    private $title;

    /**
     * @var string|null
     */
    private $icon;

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
}
