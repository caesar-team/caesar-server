<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Link;
use App\Entity\User;
use App\Model\Query\ItemListQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class ItemRepository extends EntityRepository
{
    public function getByQuery(ItemListQuery $query): array
    {
        $qb = $this->createQueryBuilder('item');

        return $qb
            ->join('item.parentList', 'parentList')
            ->where($qb->expr()->eq('parentList', ':list'))
            ->setParameter('list', $query->list)
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

    public function findByLink(Link $link): Item
    {
        $user = $link->getGuestUser();
        $qb = $this->createQueryBuilder('item');

        return $qb
            ->join('item.parentList', 'list')
            ->join(User::class, 'user', Join::WITH, 'user.inbox = list')
            ->where($qb->expr()->eq('user.id', ':user'))
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
