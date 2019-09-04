<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use App\Model\Query\ItemListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function getByQuery(ItemListQuery $query): array
    {
        $qb = $this->createQueryBuilder('item');

        return $qb
            ->join('item.parentList', 'parentList')
            ->where($qb->expr()->eq('parentList', ':list'))
            ->andWhere('item.status =:status')
            ->setParameter('list', $query->list)
            ->setParameter('status', Item::STATUS_FINISHED)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     *
     * @return Item[]|iterable
     */
    public function getFavoritesItems(User $user): iterable
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->innerJoin('item.parentList', 'list')
            ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->where('user.id = :user')
            ->andWhere('item.favorite = true')
            ->setParameter('user', $user->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCount(): int
    {
        $qb = $this->createQueryBuilder('item');
        $qb->select($qb->expr()->count('item.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
