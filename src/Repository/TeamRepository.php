<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
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
    public function findByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('team');
        $qb->join('team.userTeams', 'userTeams');
        $qb->where('userTeams.user =:user');
        $qb->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Directory $directory
     * @return Team|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByDirectory(Directory $directory): ?Team
    {
        $qb = $this->createQueryBuilder('team');
        $qb->where('team.trash =:directory');
        $qb->setParameter('directory', $directory);
        if ($directory->getParentList() instanceof Directory) {
            $qb->orWhere('team.lists =:parentList');
            $qb->setParameter('parentList', $directory->getParentList());
        }
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllExcept(array $memberships): array
    {
        $qb = $this->createQueryBuilder('team');

        if (0 < count($memberships)) {
            $qb
                ->where('team.id NOT IN (:teams)')
                ->setParameter('teams', $memberships)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}