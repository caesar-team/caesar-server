<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
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
 *     "team_member_invite",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_member_invite",
 *         parameters={ "team": "expr(object.getId())", "user": "__USER__" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::INVITE'), object.getUserTeam()))"
 *     )
 * )
 */
class TeamView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="Title")
     */
    private ?string $title;

    /**
     * @var MemberShortView[]
     *
     * @SWG\Property(type="array", @Model(type=MemberShortView::class))
     */
    private array $users;

    /**
     * @SWG\Property(type="string", example="Data icon")
     */
    private ?string $icon;

    /**
     * @SWG\Property(type="string", enum={"default", "other"})
     */
    private ?string $type;

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
        $this->users = [];
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

    /**
     * @return MemberShortView[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param MemberShortView[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
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
