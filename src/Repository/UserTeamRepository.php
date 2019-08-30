<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
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

    public function remove(UserTeam $userTeam): void
    {
        $this->_em->remove($userTeam);
        $this->_em->flush();
    }

    /**
     * @param User $user
     * @param Team $team
     * @return UserTeam|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByUserAndTeam(User $user, Team $team): ?UserTeam
    {
        $qb = $this->createQueryBuilder('userTeam');
        $qb->where('userTeam.user =:user');
        $qb->andWhere('userTeam.team =:team');
        $qb->setParameter('user', $user);
        $qb->setParameter('team', $team);
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}