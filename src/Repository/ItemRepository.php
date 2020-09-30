<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\NodeEnumType;
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
        ;

        if (null === $team) {
            $queryBuilder
                ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list OR user = item.owner')
                ->where('user.id = :user')
                ->andWhere('item.team IS NULL')
                ->andWhere('item.favorite = true')
                ->setParameter('user', $user->getId())
            ;
        } else {
            $queryBuilder
                ->andWhere('item.team = :team')
                ->andWhere('item.teamFavorite LIKE :user_like')
                ->setParameter('user_like', '%'.$user->getId()->toString().'%')
                ->setParameter('team', $team)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getTeamKeyPairByUser(User $user, Team $team): ?Item
    {
        $queryBuilder = $this
            ->createQueryBuilder('item')
            ->where('item.team = :team')
            ->andWhere('item.owner = :user')
            ->andWhere('item.type = :type')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
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

    public function resetOwnerTeamItems(Team $team, User $oldOwner, User $newOwner): void
    {
        $queryBuilder = $this->createQueryBuilder('item');

        $queryBuilder
            ->update()
            ->set('item.owner', ':owner')
            ->where('item.owner = :user')
            ->andWhere('item.team = :team')
            ->andWhere('item.originalItem IS NULL')
            ->setParameter('user', $oldOwner)
            ->setParameter('team', $team)
            ->setParameter('owner', $newOwner)
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function getCountOriginalItems(): int
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->select('COUNT(1)')
            ->where('item.originalItem IS NULL')
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
