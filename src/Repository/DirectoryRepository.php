<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Directory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Directory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Directory[]    findAll()
 * @method Directory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Directory::class);
    }

    public function getMaxPosition(): int
    {
        $query = $this->createQueryBuilder('directory');
        $query->select('MAX(directory.sort) AS max_position');

        return (int) $query->setMaxResults(1)->getQuery()->getSingleScalarResult();
    }

    public function getUserByList(Directory $list): ?User
    {
        $queryBuilder = $this->createQueryBuilder('list');

        return $queryBuilder
            ->select('user')
            ->join(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->where($queryBuilder->expr()->eq('list', ':list'))
            ->setParameter('list', $list)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(Directory $directory): void
    {
        $this->_em->persist($directory);
        $this->_em->flush();
    }

    public function remove(Directory $directory): void
    {
        $this->_em->remove($directory);
        $this->_em->flush();
    }
}
