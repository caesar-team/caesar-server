<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\ORM\EntityManagerInterface;

class TeamManager
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param User $user
     * @param string $role
     * @param Team|null $team
     * @throws \Exception
     */
    public function addTeamToUser(User $user, string $role = UserTeam::DEFAULT_USER_ROLE, Team $team = null)
    {
        $team = $team ?: $this->findDefaultTeam();
        $userTeam = new UserTeam($user, $team, $role);
        $this->manager->persist($userTeam);
    }

    private function findDefaultTeam(): Team
    {
        $team = $this->manager->getRepository(Team::class)->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);

        if (is_null($team)) {
            throw new \LogicException('At least one team must exists');
        }

        return $team;
    }

    public function findUserTeamByAlias(User $user, string $alias): ?UserTeam
    {
        foreach ($user->getUserTeams() as $userTeam) {
            if ($alias === $userTeam->getTeam()->getAlias()) {
                return $userTeam;
            }
        }

        return null;
    }
}