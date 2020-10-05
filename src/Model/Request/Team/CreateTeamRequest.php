<?php

declare(strict_types=1);

namespace App\Model\Request\Team;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTeamRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank
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
