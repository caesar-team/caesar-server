<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

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
     * @throws NonUniqueResultException
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

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function getCount()
    {
        $qb = $this->createQueryBuilder('team');
        $qb->select($qb->expr()->count('team.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}