<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use App\Model\View\User\UserView;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_member_remove",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_member_remove",
 *         parameters={ "team": "expr(object.getTeamId())", "user": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::REMOVE'), object.getUserTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_edit",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_team_member_edit",
 *         parameters={ "team": "expr(object.getTeamId())", "user": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::EDIT'), object.getUserTeam()))"
 *     )
 * )
 */
final class MemberView
{
    /**
     * @SWG\Property(@Model(type=UserView::class))
     */
    private ?UserView $user;

    /**
     * @SWG\Property(type="string", enum=UserTeam::ROLES)
     */
    private string $teamRole;

    /**
     * @Serializer\Exclude
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
        return $this->user->getId();
    }

    public function getUser(): ?UserView
    {
        return $this->user;
    }

    public function setUser(?UserView $user): void
    {
        $this->user = $user;
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
