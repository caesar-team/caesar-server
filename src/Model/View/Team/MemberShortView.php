<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Team;
use App\Entity\UserTeam;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Hateoas\Relation(
 *     "team_member_remove",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_member_add",
 *         parameters={ "team": "expr(object.teamId)", "user": "expr(object.id)" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::USER_TEAM_REMOVE_MEMBER'), object.getTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_edit",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_team_member_edit",
 *         parameters={ "team": "expr(object.teamId)", "user": "expr(object.id)" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::USER_TEAM_EDIT'), object.getTeam()))"
 *     )
 * )
 */
final class MemberShortView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string|null
     */
    public $role;

    /**
     * @var string|null
     *
     * @Serializer\Exclude
     */
    public $teamId;

    /**
     * @var Team|null
     *
     * @Serializer\Exclude
     * @SWG\Property(type="string")
     */
    private $team;

    public static function create(UserTeam $userTeam): self
    {
        $view = new self();
        $view->id = $userTeam->getUser()->getId()->toString();
        $view->role = $userTeam->getUserRole();
        $view->team = $userTeam->getTeam();
        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $view->teamId = $userTeam->getTeam() ? $userTeam->getTeam()->getId()->toString() : null;

        return $view;
    }

    /**
     * @param UserTeam[] $usersTeams
     *
     * @return MemberShortView[]
     */
    public static function createMany(array $usersTeams): array
    {
        $list = [];
        foreach ($usersTeams as $usersTeam) {
            $list[] = MemberShortView::create($usersTeam);
        }

        return $list;
    }

    /**
     * @Groups({"excluded"})
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
