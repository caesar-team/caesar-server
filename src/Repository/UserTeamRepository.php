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
     * @param array $ids
     * @return array|UserTeam[]
     */
    public function findMembers(Team $team, array $ids = []): array
    {
        $qb = $this->createQueryBuilder('userTeam');
        $qb->where('userTeam.team =:team');
        $qb->andWhere('userTeam.userRole =:member');
        $qb->setParameter('team', $team);
        $qb->setParameter('member', UserTeam::USER_ROLE_MEMBER);

        if (0 < count($ids)) {
            $qb->andWhere('userTeam.user IN(:ids)');
            $qb->setParameter('ids', $ids);
        }

        return $qb->getQuery()->getResult();
    }
}