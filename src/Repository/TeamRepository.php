<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class TeamRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param User $user
     * @return array|Team[]
     */
    public function findByUserExceptDefault(User $user): array
    {
        $qb = $this->createQueryBuilder('team');
        $qb->join('team.userTeams', 'userTeams');
        $qb->where('userTeams.user =:user');
        $qb->andWhere('team.alias <>:default');
        $qb->setParameter('default', Team::DEFAULT_GROUP_ALIAS);
        $qb->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|Team[]
     */
    public function findAllExceptDefault(): array
    {
        $qb = $this->createQueryBuilder('team');
        $qb->where('team.alias <>:default');
        $qb->setParameter('default', Team::DEFAULT_GROUP_ALIAS);

        return $qb->getQuery()->getResult();
    }
}