<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class TeamManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private TeamRepository $repository;

    public function __construct(
        EntityManagerInterface $manager,
        TeamRepository $repository
    ) {
        $this->entityManager = $manager;
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function addTeamToUser(User $user, string $role = UserTeam::DEFAULT_USER_ROLE, Team $team = null)
    {
        $team = $team ?: $this->findDefaultTeam();
        if (null !== $user->getUserTeamByTeam($team)) {
            return;
        }

        $userTeam = new UserTeam($user, $team, $role);
        $this->entityManager->persist($userTeam);
    }

    private function findDefaultTeam(): Team
    {
        $team = $this->repository->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);

        if (is_null($team)) {
            throw new LogicException('At least one team must exists');
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
