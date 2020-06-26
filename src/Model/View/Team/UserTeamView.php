<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

final class UserTeamView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="Some title")
     */
    private ?string $title;

    /**
     * @SWG\Property(type="string", example=Team::DEFAULT_GROUP_ALIAS)
     */
    private ?string $type;

    /**
     * @SWG\Property(type="string", enum=UserTeam::ROLES)
     */
    private ?string $userRole;

    /**
     * @SWG\Property(type="string", example="2020-06-26T11:14:41+00:00")
     */
    private \DateTime $createdAt;

    /**
     * @SWG\Property(type="string", example="2020-06-26T11:14:41+00:00")
     */
    private \DateTime $updatedAt;

    /**
     * @SWG\Property(type="string", enum="Icon data")
     */
    private ?string $icon;

    /**
     * @Serializer\Exclude
     */
    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getUserRole(): ?string
    {
        return $this->userRole;
    }

    public function setUserRole(?string $userRole): void
    {
        $this->userRole = $userRole;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
