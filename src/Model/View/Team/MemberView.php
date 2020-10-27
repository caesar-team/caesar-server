<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

class MemberView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string", enum=UserTeam::ROLES)
     */
    private string $teamRole;

    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $teamId;

    /**
     * @Serializer\Exclude
     */
    private ?UserTeam $userTeam;

    public function __construct(?UserTeam $currentUserTeam, Team $team)
    {
        $this->userTeam = $currentUserTeam;
        $this->teamId = $team->getId()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTeamRole(): string
    {
        return $this->teamRole;
    }

    public function setTeamRole(string $teamRole): void
    {
        $this->teamRole = $teamRole;
    }

    public function getUserTeam(): ?UserTeam
    {
        return $this->userTeam;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }
}
