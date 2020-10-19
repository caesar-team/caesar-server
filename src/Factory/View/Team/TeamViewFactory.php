<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\Team;
use App\Entity\User;
use App\Model\View\Team\TeamView;
use Symfony\Component\Security\Core\Security;

class TeamViewFactory
{
    private MemberShortViewFactory $memberShortViewFactory;

    private Security $security;

    public function __construct(Security $security, MemberShortViewFactory $memberShortViewFactory)
    {
        $this->memberShortViewFactory = $memberShortViewFactory;
        $this->security = $security;
    }

    public function createSingle(Team $team): TeamView
    {
        $user = $this->security->getUser();
        $userTeam = $team->getUserTeamByUser($user);

        $view = new TeamView($team, $userTeam);
        $view->setId($team->getId()->toString());
        $view->setUsers(
            $this->memberShortViewFactory->createCollection(
                $team->getUserTeamsWithoutPretender()
            )
        );
        $view->setTitle($team->getTitle());
        $view->setIcon($team->getIcon());
        $view->setType($team->getAlias() ?: Team::OTHER_TYPE);
        if ($user instanceof User && null !== $userTeam) {
            $view->setPinned($team->isPinned($user));
        }

        return $view;
    }

    /**
     * @param Team[] $teams
     *
     * @return TeamView[]
     */
    public function createCollection(array $teams): array
    {
        return array_map([$this, 'createSingle'], $teams);
    }
}
