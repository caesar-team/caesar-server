<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_members",
 *     attributes={"method": "GET"},
 *     href=@Hateoas\Route(
 *         "api_team_members",
 *         parameters={ "team": "expr(object.id)" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::USER_TEAM_VIEW'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_delete",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_delete",
 *         parameters={ "team": "expr(object.id)" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::DELETE'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_add",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_member_add",
 *         parameters={ "team": "expr(object.id)", "user": "__USER__" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::USER_TEAM_EDIT'), object.getTeam()))"
 *     )
 * )
 */
class TeamView
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var MemberShortView[]
     */
    public $users = [];
    /**
     * @var ListView[]
     */
    public $lists;

    /**
     * @var string|null
     */
    public $icon;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var Team|null
     *
     * @Serializer\Exclude
     * @SWG\Property(type="string")
     */
    private $team;

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
