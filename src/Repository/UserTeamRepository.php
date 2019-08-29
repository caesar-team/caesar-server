<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class UserTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTeam::class);
    }

    /**
     * @param Team $team
     * @return array|UserTeam[]
     */
    public function findByTeam(Team $team): array
    {
        $qb = $this->createQueryBuilder('userTeam');
        $qb->where('userTeam.team =:team');
        $qb->setParameter('team', $team);

        return $qb->getQuery()->getResult();
    }
}