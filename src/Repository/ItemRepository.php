<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Query\ItemListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
            ->orderBy('item.lastUpdated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Item[]
     */
    public function getFavoritesItems(User $user, ?Team $team = null): iterable
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->innerJoin('item.parentList', 'list')
            ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list OR user = item.owner')
            ->where('user.id = :user')
            ->andWhere('item.favorite = true')
            ->setParameter('user', $user->getId());

        if (null === $team) {
            $queryBuilder->andWhere('item.team IS NULL');
        } else {
            $queryBuilder
                ->andWhere('item.team = :team')
                ->setParameter('team', $team)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Item $item): Item
    {
        $this->_em->persist($item);
        $this->_em->flush();

        return $item;
    }

    public function remove(Item $item): void
    {
        $this->_em->remove($item);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function findByParentDirectoryAndParent(Item $item): array
    {
        $qb = $this->createQueryBuilder('item');
        $qb->where('item.parentList =:parent_list');
        $qb->andWhere('item.originalItem =:item');
        $qb->setParameter('parent_list', $item->getParentList());
        $qb->setParameter('item', $item);

        return $qb->getQuery()->getResult();
    }
}
