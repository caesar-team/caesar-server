<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_delete",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_delete",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::DELETE'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_edit",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_team_edit",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::EDIT'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_create_list",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_create_list",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::CREATE'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_get_lists",
 *     attributes={"method": "GET"},
 *     href=@Hateoas\Route(
 *         "api_team_get_lists",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::SHOW'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_members",
 *     attributes={"method": "GET"},
 *     href=@Hateoas\Route(
 *         "api_team_members",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::VIEW'), object.getUserTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_add",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_member_add",
 *         parameters={ "team": "expr(object.getId())", "user": "__USER__" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::EDIT'), object.getUserTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_batch_add",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_member_batch",
 *         parameters={ "team": "expr(object.getId())"}
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::EDIT'), object.getUserTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_pinned",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_pinned_team_toggle",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::PINNED'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_unpinned",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_unpinned_team_toggle",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::PINNED'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_leave",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_member_leave",
 *         parameters={ "team": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::LEAVE'), object.getTeam()))"
 *     )
 * )
 */
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
     * @SWG\Property(type="boolean", example=false)
     */
    private bool $pinned = false;

    /**
     * @Serializer\Exclude
     */
    private Team $team;

    /**
     * @Serializer\Exclude
     */
    private ?UserTeam $userTeam;

    public function __construct(Team $team, ?UserTeam $currentUserTeam = null)
    {
        $this->team = $team;
        $this->userTeam = $currentUserTeam;
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

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): void
    {
        $this->pinned = $pinned;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getUserTeam(): ?UserTeam
    {
        return $this->userTeam;
    }
}
